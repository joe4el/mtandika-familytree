<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FamilyTree;

class FamilyTreeController extends Controller
{
        public function showPage($id = null)
    {
        $tree = DB::table('family_trees')->where('id', 1)->first();
        $treeData = $tree ? json_decode($tree->tree_data, true) : [];

        $currentNode = $id ? $this->findNodeById($treeData, $id) : $treeData;
        if (!$currentNode) $currentNode = $treeData;

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

    public function initTree()
    {
        $tree = $this->generateInitialFamilyTree();

        DB::table('family_trees')->updateOrInsert(
            ['id' => 1],
            ['tree_data' => json_encode($tree)]
        );

        return response()->json(['success' => true, 'message' => 'Tree initialized']);
    }

    private function getRootTree()
    {
        $row = DB::table('family_trees')->where('id', 1)->first();
        return $row ? json_decode($row->tree_data, true) : null;
    }

    private function saveTreeToDb($tree)
    {
        $record = FamilyTree::first();
        $record->tree_data = json_encode($tree);
        $record->save();
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

    private function addChildByParentId(&$node, $parentId, $child)
    {
        if ($node['id'] == $parentId) {
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

        private function generateNewId($tree, $parentId)
        {
            $parent = $this->findNodeById($tree, $parentId);
            $count = isset($parent['children']) ? count($parent['children']) : 0;
            return $parentId . '-' . uniqid();
        }

        public function addChild(Request $request)
        {
            $parentId = $request->input('parent_id');
            $name = $request->input('name', 'New Member');

            $tree = $this->getRootTree();
            if (!$tree) return response()->json(['success' => false, 'message' => 'Tree not found'], 404);

            $newId = $this->generateNewId($tree, $parentId);
            $child = ['id' => $newId, 'name' => $name, 'children' => []];

            $this->addChildByParentId($tree, $parentId, $child);
            $this->saveTreeToDb($tree);

            return response()->json(['success' => true, 'child' => $child]);
        }

        public function updateMember(Request $request)
        {
            $id = $request->input('id');
            $name = $request->input('name');

            $tree = $this->getRootTree();
            if (!$tree) return response()->json(['success' => false, 'message' => 'Tree not found'], 404);

            $this->updateNodeName($tree, $id, $name);
            $this->saveTreeToDb($tree);

            return response()->json(['success' => true]);
        }

         public function updateMemberField(Request $request)
    {
        $id = $request->input('id');
        $field = $request->input('field');
        $value = $request->input('value');

        $treeRow = \DB::table('family_trees')->where('id', 1)->first();
        if (!$treeRow) {
            return response()->json(['success' => false, 'message' => 'Family tree not found']);
        }

        $tree = json_decode($treeRow->tree_data, true);

        $updated = $this->updateMemberRecursively($tree, $id, $field, $value);

        if (!$updated) {
            return response()->json(['success' => false, 'message' => 'Member not found']);
        }

        \DB::table('family_trees')->where('id', 1)->update([
            'tree_data' => json_encode($tree)
        ]);

        return response()->json([
            'success' => true,
            'message' => "$field updated successfully!",
            'updatedTree' => $tree
        ]);
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

        private function updateNodeFieldById(&$node, $id, $field, $value)
        {
            if ($node['id'] == $id) {
                $node[$field] = $value;
                return true;
            }

            if (isset($node['children'])) {
                foreach ($node['children'] as &$child) {
                    if ($this->updateNodeFieldById($child, $id, $field, $value)) {
                        return true;
                    }
                }
            }

            return false;
        }

        public function updateField(Request $request, $id)
        {
                $member = FamilyMember::find($id);

                if (!$member) {
                    return response()->json(['success' => false, 'message' => 'Member not found']);
                }

                $field = $request->field;
                $value = $request->value;

                if (!in_array($field, ['dob', 'dod', 'bio'])) {
                    return response()->json(['success' => false, 'message' => 'Invalid field']);
                }

                $member->$field = $value;
                $member->save();

                return response()->json(['success' => true]);
        }

        public function deleteMember(Request $request)
        {
            $id = $request->input('id');

            $tree = $this->getRootTree();
            if (!$tree) return response()->json(['success' => false, 'message' => 'Tree not found'], 404);

            if ($tree['id'] == $id) {
                return response()->json(['success' => false, 'message' => 'Cannot delete root node'], 400);
            }

            $this->deleteNodeById($tree, $id);
            $this->saveTreeToDb($tree);

            return response()->json(['success' => true]);
        }

        public function showBio($id)
        {
            $tree = $this->getRootTree();
            $member = $this->findNodeById($tree, $id);

            if (!$member) abort(404);

            return view('familyviews.biography', ['member' => $member]);
        }

        public function saveWholeTree(Request $request)
            {
                $data = $request->input('tree');
                $id = $request->input('id'); 

                $tree = $this->getRootTree();
                if (!$tree) {
                    return response()->json(['success' => false, 'message' => 'Tree not found']);
                }

                $this->updateBranch($tree, $id, $data);
                $this->saveTreeToDb($tree);

                return response()->json(['success' => true]);
            }

        private function findParentId($tree, $memberId, $parentId = null)
        {
                if ($tree['id'] === $memberId) {
                    return $parentId;
                }

                if (!empty($tree['children'])) {
                    foreach ($tree['children'] as $child) {
                        $found = $this->findParentId($child, $memberId, $tree['id']);
                        if ($found) {
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

        private function generateInitialFamilyTree()
            {
                $generateChildren = function ($parentId, $generation) use (&$generateChildren) {
                    $children = [];

                    if ($generation > 3) return [];

                    $numChildren = 0;

                    if ($generation === 1) {
                        $numChildren = 3;
                    } elseif ($generation === 2) {
                        if (str_ends_with($parentId, '-1')) $numChildren = 2;  // first born
                        elseif (str_ends_with($parentId, '-2')) $numChildren = 7; // second born
                        elseif (str_ends_with($parentId, '-3')) $numChildren = 3; // third born
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




