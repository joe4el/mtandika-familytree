<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\FamilyTree;

class FamilyTreeController extends Controller
{
    // ==========================================
    // VIEW METHODS (Existing)
    // ==========================================
    
    public function showPage($id = null)
    {
        $tree = DB::table('family_trees')->where('id', 1)->first();
        $treeData = $tree ? json_decode($tree->tree_data, true) : [];

        $currentNode = $treeData;

        if ($id !== null) {
            $foundNode = $this->findNodeById($treeData, $id);
            if ($foundNode !== null) {
                $currentNode = $foundNode;
            }
        }

        $parentId = $this->findParentId($treeData, $id);

        return view('familyviews.familytree', [
            'treeData' => $treeData,
            'currentNode' => $currentNode,
            'parentId' => $parentId, 
        ]);
    }

    public function show($id = null)
    {
        $tree = $this->getRootTree();
        if (!$tree) {
            return view('familytree', ['currentNode' => null]);
        }

        $currentNode = $id ? $this->findNodeById($tree, $id) : $tree;

        return view('familytree', [
            'currentNode' => $currentNode,
            'parentId' => $this->findParentId($tree, $id)
        ]);
    }

    public function showBio($id)
    {
        $tree = $this->getRootTree();
        $member = $this->findNodeById($tree, $id);

        if (!$member) abort(404);

        return view('familyviews.biography', ['member' => $member]);
    }

    // ==========================================
    // CORE CRUD APIs
    // ==========================================

    /**
     * Get entire tree
     * GET /api/family-trees/{treeId}
     */
    public function getTree($treeId)
    {
        $tree = DB::table('family_trees')->where('id', $treeId)->first();
        
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => json_decode($tree->tree_data, true),
            'meta' => [
                'tree_id' => $tree->id,
                'created_at' => $tree->created_at ?? null,
                'updated_at' => $tree->updated_at ?? null
            ]
        ]);
    }

    /**
     * Get single member with full details
     * GET /api/family-trees/{treeId}/members/{memberId}
     */
    public function getMember($treeId, $memberId)
    {
        $tree = $this->getTreeById($treeId);
        
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $member = $this->findNodeById($tree, $memberId);
        
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $parentId = $this->findParentId($tree, $memberId);
        $siblings = $this->findSiblings($tree, $memberId);

        return response()->json([
            'success' => true,
            'data' => [
                'member' => $member,
                'parent_id' => $parentId,
                'siblings' => $siblings,
                'children_count' => isset($member['children']) ? count($member['children']) : 0
            ]
        ]);
    }

    /**
     * Get flat list of all members
     * GET /api/family-trees/{treeId}/members
     */
    public function listMembers($treeId)
    {
        $tree = $this->getTreeById($treeId);
        
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $members = [];
        $this->flattenTree($tree, $members);

        return response()->json([
            'success' => true,
            'data' => $members,
            'meta' => [
                'total_count' => count($members)
            ]
        ]);
    }

    /**
     * Create new tree
     * POST /api/family-trees
     */
    public function createTree(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'root_member_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $treeData = [
            'id' => 'root',
            'name' => $request->root_member_name,
            'children' => []
        ];

        $treeId = DB::table('family_trees')->insertGetId([
            'tree_data' => json_encode($treeData),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tree created successfully',
            'data' => [
                'tree_id' => $treeId,
                'tree_data' => $treeData
            ]
        ], 201);
    }

    /**
     * Initialize tree with sample data
     * POST /api/family-trees/init
     */
    public function initTree()
    {
        $tree = $this->generateInitialFamilyTree();

        DB::table('family_trees')->updateOrInsert(
            ['id' => 1],
            ['tree_data' => json_encode($tree), 'updated_at' => now()]
        );

        return response()->json([
            'success' => true, 
            'message' => 'Tree initialized',
            'data' => $tree
        ]);
    }

    /**
     * Update member completely
     * PUT /api/family-trees/{treeId}/members/{memberId}
     */
    public function updateMemberComplete(Request $request, $treeId, $memberId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'dob' => 'sometimes|nullable|date',
            'dod' => 'sometimes|nullable|date',
            'bio' => 'sometimes|nullable|string',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'occupation' => 'sometimes|nullable|string|max:255',
            'location' => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $updated = $this->updateNodeMultipleFields($tree, $memberId, $request->all());

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $this->saveTreeToDbById($treeId, $tree);

        return response()->json([
            'success' => true,
            'message' => 'Member updated successfully',
            'data' => $this->findNodeById($tree, $memberId)
        ]);
    }

    /**
     * Delete entire tree
     * DELETE /api/family-trees/{treeId}
     */
    public function deleteTree($treeId)
    {
        $deleted = DB::table('family_trees')->where('id', $treeId)->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tree deleted successfully'
        ]);
    }

    // ==========================================
    // EXISTING APIs (Improved)
    // ==========================================

    /**
     * Add child member
     * POST /api/family-trees/{treeId}/members/{memberId}/children
     */
    public function addChild(Request $request, $treeId = null, $memberId = null)
    {
        // Support both old and new route formats
        $parentId = $memberId ?? $request->input('parent_id');
        $name = $request->input('name', 'New Member');
        $actualTreeId = $treeId ?? 1;

        $validator = Validator::make([
            'parent_id' => $parentId,
            'name' => $name
        ], [
            'parent_id' => 'required|string',
            'name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tree = $this->getTreeById($actualTreeId);
        if (!$tree) {
            return response()->json([
                'success' => false, 
                'message' => 'Tree not found'
            ], 404);
        }

        $parent = $this->findNodeById($tree, $parentId);
        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'Parent member not found'
            ], 404);
        }

        $newId = $this->generateNewId($tree, $parentId);
        $child = [
            'id' => $newId, 
            'name' => $name, 
            'children' => [],
            'dob' => null,
            'dod' => null,
            'bio' => null
        ];

        $this->addChildByParentId($tree, $parentId, $child);
        $this->saveTreeToDbById($actualTreeId, $tree);

        return response()->json([
            'success' => true,
            'message' => 'Child added successfully',
            'data' => $child
        ], 201);
    }

    /**
     * Update member name (legacy support)
     * POST /api/family-trees/members/update
     */
    public function updateMember(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');

        $tree = $this->getRootTree();
        if (!$tree) {
            return response()->json([
                'success' => false, 
                'message' => 'Tree not found'
            ], 404);
        }

        $this->updateNodeName($tree, $id, $name);
        $this->saveTreeToDb($tree);

        return response()->json([
            'success' => true,
            'message' => 'Member name updated successfully'
        ]);
    }

    /**
     * Update specific member field
     * PATCH /api/family-trees/members/{memberId}/field
     */
    public function updateMemberField(Request $request, $memberId = null)
    {
        $id = $memberId ?? $request->input('id');
        $field = $request->input('field');
        $value = $request->input('value');

        $validator = Validator::make([
            'id' => $id,
            'field' => $field,
            'value' => $value
        ], [
            'id' => 'required|string',
            'field' => 'required|string|in:name,dob,dod,bio,gender,occupation,location',
            'value' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $treeRow = DB::table('family_trees')->where('id', 1)->first();
        if (!$treeRow) {
            return response()->json([
                'success' => false, 
                'message' => 'Family tree not found'
            ], 404);
        }

        $tree = json_decode($treeRow->tree_data, true);
        $updated = $this->updateMemberRecursively($tree, $id, $field, $value);

        if (!$updated) {
            return response()->json([
                'success' => false, 
                'message' => 'Member not found'
            ], 404);
        }

        DB::table('family_trees')->where('id', 1)->update([
            'tree_data' => json_encode($tree),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($field) . ' updated successfully',
            'data' => $this->findNodeById($tree, $id)
        ]);
    }

    /**
     * Delete member
     * DELETE /api/family-trees/{treeId}/members/{memberId}
     */
    public function deleteMember(Request $request, $treeId = null, $memberId = null)
    {
        $id = $memberId ?? $request->input('id');
        $actualTreeId = $treeId ?? 1;

        $tree = $this->getTreeById($actualTreeId);
        if (!$tree) {
            return response()->json([
                'success' => false, 
                'message' => 'Tree not found'
            ], 404);
        }

        if ($tree['id'] == $id) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete root node'
            ], 400);
        }

        $deleted = $this->deleteNodeById($tree, $id);
        
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $this->saveTreeToDbById($actualTreeId, $tree);

        return response()->json([
            'success' => true,
            'message' => 'Member deleted successfully'
        ]);
    }

    /**
     * Save whole tree branch
     * PUT /api/family-trees/{treeId}/branch
     */
    public function saveWholeTree(Request $request, $treeId = null)
    {
        $data = $request->input('tree');
        $id = $request->input('id');
        $actualTreeId = $treeId ?? 1;

        if (!$data || !$id) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields: tree and id'
            ], 422);
        }

        $tree = $this->getTreeById($actualTreeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $this->updateBranch($tree, $id, $data);
        $this->saveTreeToDbById($actualTreeId, $tree);

        return response()->json([
            'success' => true,
            'message' => 'Branch updated successfully'
        ]);
    }

    // ==========================================
    // RELATIONSHIP MANAGEMENT APIs
    // ==========================================

    /**
     * Add spouse to member
     * POST /api/family-trees/{treeId}/members/{memberId}/spouse
     */
    public function addSpouse(Request $request, $treeId, $memberId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'marriage_date' => 'sometimes|nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $member = $this->findNodeById($tree, $memberId);
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $spouseData = [
            'name' => $request->name,
            'marriage_date' => $request->marriage_date ?? null,
            'added_at' => now()->toDateTimeString()
        ];

        $this->addSpouseToMember($tree, $memberId, $spouseData);
        $this->saveTreeToDbById($treeId, $tree);

        return response()->json([
            'success' => true,
            'message' => 'Spouse added successfully',
            'data' => $spouseData
        ], 201);
    }

    /**
     * Add parent to member
     * POST /api/family-trees/{treeId}/members/{memberId}/parents
     */
    public function addParent(Request $request, $treeId, $memberId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'relationship' => 'required|in:father,mother',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        // Add parent info to member node
        $updated = $this->addParentInfo($tree, $memberId, $request->relationship, $request->name);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $this->saveTreeToDbById($treeId, $tree);

        return response()->json([
            'success' => true,
            'message' => 'Parent information added successfully',
            'data' => $this->findNodeById($tree, $memberId)
        ]);
    }

    // ==========================================
    // SEARCH & QUERY APIs
    // ==========================================

    /**
     * Search members by name
     * GET /api/family-trees/{treeId}/search?q={query}
     */
    public function searchMembers(Request $request, $treeId)
    {
        $query = $request->input('q');

        if (!$query || strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 2 characters'
            ], 422);
        }

        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $results = [];
        $this->searchInTree($tree, $query, $results);

        return response()->json([
            'success' => true,
            'data' => $results,
            'meta' => [
                'query' => $query,
                'count' => count($results)
            ]
        ]);
    }

    /**
     * Get all ancestors of a member
     * GET /api/family-trees/{treeId}/members/{memberId}/ancestors
     */
    public function getAncestors($treeId, $memberId)
    {
        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $ancestors = [];
        $this->collectAncestors($tree, $memberId, $ancestors);

        return response()->json([
            'success' => true,
            'data' => array_reverse($ancestors), // Root first
            'meta' => [
                'count' => count($ancestors)
            ]
        ]);
    }

    /**
     * Get all descendants of a member
     * GET /api/family-trees/{treeId}/members/{memberId}/descendants
     */
    public function getDescendants($treeId, $memberId)
    {
        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $member = $this->findNodeById($tree, $memberId);
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $descendants = [];
        if (isset($member['children'])) {
            $this->flattenTree($member, $descendants, true); // Skip root
        }

        return response()->json([
            'success' => true,
            'data' => $descendants,
            'meta' => [
                'count' => count($descendants)
            ]
        ]);
    }

    /**
     * Get siblings of a member
     * GET /api/family-trees/{treeId}/members/{memberId}/siblings
     */
    public function getSiblings($treeId, $memberId)
    {
        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $siblings = $this->findSiblings($tree, $memberId);

        return response()->json([
            'success' => true,
            'data' => $siblings,
            'meta' => [
                'count' => count($siblings)
            ]
        ]);
    }

    /**
     * Get all members in a specific generation level
     * GET /api/family-trees/{treeId}/generation/{level}
     */
    public function getGeneration($treeId, $level)
    {
        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $members = [];
        $this->collectByGeneration($tree, 0, $level, $members);

        return response()->json([
            'success' => true,
            'data' => $members,
            'meta' => [
                'generation_level' => $level,
                'count' => count($members)
            ]
        ]);
    }

    // ==========================================
    // BULK OPERATIONS APIs
    // ==========================================

    /**
     * Batch create members
     * POST /api/family-trees/{treeId}/members/batch
     */
    public function batchCreateMembers(Request $request, $treeId)
    {
        $validator = Validator::make($request->all(), [
            'members' => 'required|array|min:1',
            'members.*.parent_id' => 'required|string',
            'members.*.name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $created = [];
        $errors = [];

        foreach ($request->members as $index => $memberData) {
            $parentId = $memberData['parent_id'];
            $name = $memberData['name'];

            $parent = $this->findNodeById($tree, $parentId);
            if (!$parent) {
                $errors[] = [
                    'index' => $index,
                    'error' => "Parent not found: {$parentId}"
                ];
                continue;
            }

            $newId = $this->generateNewId($tree, $parentId);
            $child = [
                'id' => $newId,
                'name' => $name,
                'children' => [],
                'dob' => $memberData['dob'] ?? null,
                'dod' => $memberData['dod'] ?? null,
                'bio' => $memberData['bio'] ?? null,
            ];

            $this->addChildByParentId($tree, $parentId, $child);
            $created[] = $child;
        }

        $this->saveTreeToDbById($treeId, $tree);

        return response()->json([
            'success' => true,
            'message' => count($created) . ' members created successfully',
            'data' => [
                'created' => $created,
                'errors' => $errors
            ]
        ], 201);
    }

    /**
     * Batch update members
     * PUT /api/family-trees/{treeId}/members/batch
     */
    public function batchUpdateMembers(Request $request, $treeId)
    {
        $validator = Validator::make($request->all(), [
            'updates' => 'required|array|min:1',
            'updates.*.id' => 'required|string',
            'updates.*.fields' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $updated = [];
        $errors = [];

        foreach ($request->updates as $index => $updateData) {
            $memberId = $updateData['id'];
            $fields = $updateData['fields'];

            $success = $this->updateNodeMultipleFields($tree, $memberId, $fields);
            
            if ($success) {
                $updated[] = $memberId;
            } else {
                $errors[] = [
                    'index' => $index,
                    'id' => $memberId,
                    'error' => 'Member not found'
                ];
            }
        }

        $this->saveTreeToDbById($treeId, $tree);

        return response()->json([
            'success' => true,
            'message' => count($updated) . ' members updated successfully',
            'data' => [
                'updated_count' => count($updated),
                'updated_ids' => $updated,
                'errors' => $errors
            ]
        ]);
    }

    /**
     * Batch delete members
     * DELETE /api/family-trees/{treeId}/members/batch
     */
    public function batchDeleteMembers(Request $request, $treeId)
    {
        $validator = Validator::make($request->all(), [
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $deleted = [];
        $errors = [];

        foreach ($request->member_ids as $memberId) {
            if ($tree['id'] == $memberId) {
                $errors[] = [
                    'id' => $memberId,
                    'error' => 'Cannot delete root node'
                ];
                continue;
            }

            $success = $this->deleteNodeById($tree, $memberId);
            
            if ($success) {
                $deleted[] = $memberId;
            } else {
                $errors[] = [
                    'id' => $memberId,
                    'error' => 'Member not found'
                ];
            }
        }

        $this->saveTreeToDbById($treeId, $tree);

        return response()->json([
            'success' => true,
            'message' => count($deleted) . ' members deleted successfully',
            'data' => [
                'deleted_count' => count($deleted),
                'deleted_ids' => $deleted,
                'errors' => $errors
            ]
        ]);
    }

    // ==========================================
    // IMPORT/EXPORT APIs
    // ==========================================

    /**
     * Export tree in various formats
     * GET /api/family-trees/{treeId}/export?format={json|csv}
     */
    public function exportTree($treeId, Request $request)
    {
        $format = $request->input('format', 'json');

        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        switch ($format) {
            case 'json':
                return response()->json([
                    'success' => true,
                    'data' => $tree,
                    'exported_at' => now()->toDateTimeString()
                ]);

            case 'csv':
                $members = [];
                $this->flattenTree($tree, $members);
                
                $csv = "ID,Name,DOB,DOD,Bio,Parent ID\n";
                foreach ($members as $member) {
                    $parentId = $this->findParentId($tree, $member['id']) ?? '';
                    $csv .= sprintf(
                        '"%s","%s","%s","%s","%s","%s"' . "\n",
                        $member['id'],
                        $member['name'] ?? '',
                        $member['dob'] ?? '',
                        $member['dod'] ?? '',
                        str_replace('"', '""', $member['bio'] ?? ''),
                        $parentId
                    );
                }

                return response($csv)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="family_tree_' . $treeId . '.csv"');

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported format. Use: json, csv'
                ], 400);
        }
    }

    /**
     * Import tree from JSON
     * POST /api/family-trees/{treeId}/import
     */
    public function importTree(Request $request, $treeId)
    {
        $validator = Validator::make($request->all(), [
            'tree_data' => 'required|array',
            'tree_data.id' => 'required|string',
            'tree_data.name' => 'required|string',
            'merge' => 'sometimes|boolean' // Whether to merge with existing or replace
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $existingTree = $this->getTreeById($treeId);
        $importData = $request->tree_data;
        $merge = $request->input('merge', false);

        if ($merge && $existingTree) {
            // Merge logic: append imported data as children
            if (!isset($existingTree['children'])) {
                $existingTree['children'] = [];
            }
            $existingTree['children'][] = $importData;
            $finalTree = $existingTree;
        } else {
            // Replace entirely
            $finalTree = $importData;
        }

        $this->saveTreeToDbById($treeId, $finalTree);

        return response()->json([
            'success' => true,
            'message' => 'Tree imported successfully',
            'data' => $finalTree
        ]);
    }

    // ==========================================
    // STATISTICS & ANALYTICS APIs
    // ==========================================

    /**
     * Get tree statistics
     * GET /api/family-trees/{treeId}/stats
     */
    public function getTreeStatistics($treeId)
    {
        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $stats = [
            'total_members' => 0,
            'total_generations' => 0,
            'members_with_bio' => 0,
            'members_with_dob' => 0,
            'members_with_dod' => 0,
            'deceased_count' => 0,
            'living_count' => 0,
            'male_count' => 0,
            'female_count' => 0,
            'oldest_member' => null,
            'youngest_member' => null,
        ];

        $members = [];
        $this->flattenTree($tree, $members);
        $stats['total_members'] = count($members);

        $maxDepth = 0;
        $this->calculateMaxDepth($tree, 0, $maxDepth);
        $stats['total_generations'] = $maxDepth + 1;

        $oldestYear = null;
        $youngestYear = null;

        foreach ($members as $member) {
            if (!empty($member['bio'])) $stats['members_with_bio']++;
            if (!empty($member['dob'])) {
                $stats['members_with_dob']++;
                $year = date('Y', strtotime($member['dob']));
                if (!$oldestYear || $year < $oldestYear) {
                    $oldestYear = $year;
                    $stats['oldest_member'] = $member;
                }
                if (!$youngestYear || $year > $youngestYear) {
                    $youngestYear = $year;
                    $stats['youngest_member'] = $member;
                }
            }
            if (!empty($member['dod'])) {
                $stats['members_with_dod']++;
                $stats['deceased_count']++;
            } else {
                $stats['living_count']++;
            }
            
            if (isset($member['gender'])) {
                if ($member['gender'] === 'male') $stats['male_count']++;
                if ($member['gender'] === 'female') $stats['female_count']++;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get timeline of events
     * GET /api/family-trees/{treeId}/timeline
     */
    public function getTimeline($treeId)
    {
        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $events = [];
        $members = [];
        $this->flattenTree($tree, $members);

        foreach ($members as $member) {
            if (!empty($member['dob'])) {
                $events[] = [
                    'type' => 'birth',
                    'date' => $member['dob'],
                    'member_id' => $member['id'],
                    'member_name' => $member['name'],
                    'description' => $member['name'] . ' was born'
                ];
            }
            if (!empty($member['dod'])) {
                $events[] = [
                    'type' => 'death',
                    'date' => $member['dod'],
                    'member_id' => $member['id'],
                    'member_name' => $member['name'],
                    'description' => $member['name'] . ' passed away'
                ];
            }
        }

        // Sort by date
        usort($events, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return response()->json([
            'success' => true,
            'data' => $events,
            'meta' => [
                'total_events' => count($events)
            ]
        ]);
    }

    // ==========================================
    // VALIDATION & INTEGRITY APIs
    // ==========================================

    /**
     * Validate tree for issues
     * GET /api/family-trees/{treeId}/validate
     */
    public function validateTree($treeId)
    {
        $tree = $this->getTreeById($treeId);
        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Tree not found'
            ], 404);
        }

        $issues = [];
        $members = [];
        $this->flattenTree($tree, $members);

        // Check for duplicate IDs
        $ids = array_column($members, 'id');
        $duplicates = array_diff_assoc($ids, array_unique($ids));
        if (!empty($duplicates)) {
            $issues[] = [
                'severity' => 'critical',
                'type' => 'duplicate_ids',
                'message' => 'Duplicate member IDs found',
                'details' => array_values(array_unique($duplicates))
            ];
        }

        // Check for members without names
        foreach ($members as $member) {
            if (empty($member['name'])) {
                $issues[] = [
                    'severity' => 'warning',
                    'type' => 'missing_name',
                    'message' => 'Member without name',
                    'member_id' => $member['id']
                ];
            }

            // Check for invalid dates
            if (!empty($member['dob']) && !empty($member['dod'])) {
                if (strtotime($member['dob']) > strtotime($member['dod'])) {
                    $issues[] = [
                        'severity' => 'error',
                        'type' => 'invalid_dates',
                        'message' => 'Date of birth is after date of death',
                        'member_id' => $member['id'],
                        'member_name' => $member['name']
                    ];
                }
            }

            // Check for orphaned nodes (except root)
            if ($member['id'] !== $tree['id']) {
                $parentId = $this->findParentId($tree, $member['id']);
                if (!$parentId) {
                    $issues[] = [
                        'severity' => 'error',
                        'type' => 'orphaned_node',
                        'message' => 'Member has no parent',
                        'member_id' => $member['id'],
                        'member_name' => $member['name']
                    ];
                }
            }
        }

        $isValid = empty(array_filter($issues, function($issue) {
            return in_array($issue['severity'], ['critical', 'error']);
        }));

        return response()->json([
            'success' => true,
            'data' => [
                'is_valid' => $isValid,
                'issues' => $issues,
                'total_issues' => count($issues),
                'critical_count' => count(array_filter($issues, fn($i) => $i['severity'] === 'critical')),
                'error_count' => count(array_filter($issues, fn($i) => $i['severity'] === 'error')),
                'warning_count' => count(array_filter($issues, fn($i) => $i['severity'] === 'warning')),
            ]
        ]);
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    private function getRootTree()
    {
        $row = DB::table('family_trees')->where('id', 1)->first();
        return $row ? json_decode($row->tree_data, true) : null;
    }

    private function getTreeById($treeId)
    {
        $row = DB::table('family_trees')->where('id', $treeId)->first();
        return $row ? json_decode($row->tree_data, true) : null;
    }

    private function saveTreeToDb($tree)
    {
        DB::table('family_trees')->where('id', 1)->update([
            'tree_data' => json_encode($tree),
            'updated_at' => now()
        ]);
    }

    private function saveTreeToDbById($treeId, $tree)
    {
        DB::table('family_trees')->where('id', $treeId)->update([
            'tree_data' => json_encode($tree),
            'updated_at' => now()
        ]);
    }

    private function findNodeById($node, $id)
    {
        if (!is_array($node)) return null;
        if (isset($node['id']) && $node['id'] == $id) return $node;

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $result = $this->findNodeById($child, $id);
                if ($result) return $result;
            }
        }

        return null;
    }

    private function updateNodeName(&$node, $id, $name)
    {
        if (!is_array($node)) return false;
        if ($node['id'] == $id) {
            $node['name'] = $name;
            return true;
        }
        if (!empty($node['children'])) {
            foreach ($node['children'] as &$child) {
                if ($this->updateNodeName($child, $id, $name)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function updateMemberRecursively(&$node, $id, $field, $value)
    {
        if ($node['id'] == $id) {
            $node[$field] = $value;
            return true;
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as &$child) {
                if ($this->updateMemberRecursively($child, $id, $field, $value)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function updateNodeMultipleFields(&$node, $id, $fields)
    {
        if ($node['id'] == $id) {
            foreach ($fields as $field => $value) {
                $node[$field] = $value;
            }
            return true;
        }

        if (isset($node['children'])) {
            foreach ($node['children'] as &$child) {
                if ($this->updateNodeMultipleFields($child, $id, $fields)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function addChildByParentId(&$node, $parentId, $child)
    {
        if ($node['id'] == $parentId) {
            if (!isset($node['children'])) {
                $node['children'] = [];
            }
            $node['children'][] = $child;
            return true;
        }
        if (!empty($node['children'])) {
            foreach ($node['children'] as &$childNode) {
                if ($this->addChildByParentId($childNode, $parentId, $child)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function deleteNodeById(&$node, $id)
    {
        if (empty($node['children'])) return false;

        foreach ($node['children'] as $i => &$child) {
            if ($child['id'] == $id) {
                array_splice($node['children'], $i, 1);
                return true;
            } else {
                if ($this->deleteNodeById($child, $id)) return true;
            }
        }
        return false;
    }

    private function findParentId($tree, $memberId, $parentId = null)
    {
        if (!is_array($tree)) {
            return null;
        }

        if (isset($tree['id']) && $tree['id'] === $memberId) {
            return $parentId;
        }

        if (!empty($tree['children']) && is_array($tree['children'])) {
            foreach ($tree['children'] as $child) {
                $found = $this->findParentId($child, $memberId, $tree['id'] ?? null);

                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function updateBranch(&$node, $id, $newBranch)
    {
        if ($node['id'] === $id) {
            $node = $newBranch;
            return true;
        }

        if (!empty($node['children'])) {
            foreach ($node['children'] as &$child) {
                if ($this->updateBranch($child, $id, $newBranch)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function generateNewId($tree, $parentId)
    {
        return $parentId . '-' . uniqid();
    }

    private function flattenTree($node, &$result, $skipRoot = false)
    {
        if (!$skipRoot && is_array($node) && isset($node['id'])) {
            $result[] = $node;
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->flattenTree($child, $result);
            }
        }
    }

    private function searchInTree($node, $query, &$results)
    {
        if (is_array($node) && isset($node['name'])) {
            if (stripos($node['name'], $query) !== false) {
                $results[] = $node;
            }
        }

        if (isset($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->searchInTree($child, $query, $results);
            }
        }
    }

    private function collectAncestors($tree, $memberId, &$ancestors, $currentPath = [])
    {
        if (is_array($tree) && isset($tree['id'])) {
            $currentPath[] = $tree;

            if ($tree['id'] === $memberId) {
                // Found the member, add all ancestors
                array_pop($currentPath); // Remove the member itself
                $ancestors = $currentPath;
                return true;
            }

            if (isset($tree['children'])) {
                foreach ($tree['children'] as $child) {
                    if ($this->collectAncestors($child, $memberId, $ancestors, $currentPath)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function findSiblings($tree, $memberId)
    {
        $parentId = $this->findParentId($tree, $memberId);
        
        if (!$parentId) {
            return [];
        }

        $parent = $this->findNodeById($tree, $parentId);
        
        if (!$parent || !isset($parent['children'])) {
            return [];
        }

        $siblings = array_filter($parent['children'], function($child) use ($memberId) {
            return $child['id'] !== $memberId;
        });

        return array_values($siblings);
    }

    private function collectByGeneration($node, $currentLevel, $targetLevel, &$result)
    {
        if ($currentLevel === $targetLevel) {
            $result[] = $node;
            return;
        }

        if (isset($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->collectByGeneration($child, $currentLevel + 1, $targetLevel, $result);
            }
        }
    }

    private function calculateMaxDepth($node, $currentDepth, &$maxDepth)
    {
        if ($currentDepth > $maxDepth) {
            $maxDepth = $currentDepth;
        }

        if (isset($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->calculateMaxDepth($child, $currentDepth + 1, $maxDepth);
            }
        }
    }

    private function addSpouseToMember(&$tree, $memberId, $spouseData)
    {
        if ($tree['id'] === $memberId) {
            if (!isset($tree['spouses'])) {
                $tree['spouses'] = [];
            }
            $tree['spouses'][] = $spouseData;
            return true;
        }

        if (isset($tree['children'])) {
            foreach ($tree['children'] as &$child) {
                if ($this->addSpouseToMember($child, $memberId, $spouseData)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function addParentInfo(&$tree, $memberId, $relationship, $name)
    {
        if ($tree['id'] === $memberId) {
            if (!isset($tree['parents'])) {
                $tree['parents'] = [];
            }
            $tree['parents'][$relationship] = $name;
            return true;
        }

        if (isset($tree['children'])) {
            foreach ($tree['children'] as &$child) {
                if ($this->addParentInfo($child, $memberId, $relationship, $name)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function generateInitialFamilyTree()
    {
        $generateChildren = function ($parentId, $generation) use (&$generateChildren) {
            $children = [];

            if ($generation > 3) return [];

            $numChildren = 0;

            if ($generation === 1) {
                $numChildren = 3;
            } elseif ($generation === 2) {
                if (str_ends_with($parentId, '-1')) $numChildren = 2;
                elseif (str_ends_with($parentId, '-2')) $numChildren = 7;
                elseif (str_ends_with($parentId, '-3')) $numChildren = 3;
            }

            for ($i = 1; $i <= $numChildren; $i++) {
                $id = "{$parentId}-{$i}";
                $children[] = [
                    'id' => $id,
                    'name' => "Member {$id}",
                    'children' => $generateChildren($id, $generation + 1)
                ];
            }

            return $children;
        };

        return [
            'id' => 'root',
            'name' => 'Generation 1 - Root',
            'children' => $generateChildren('root', 1)
        ];
    }
}