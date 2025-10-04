<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FamilyMember;
use Illuminate\Support\Facades\DB;

class FamilyTreeController extends Controller
{
    
   public function showPage($id = null)
{
    $tree = DB::table('family_trees')->where('id', 1)->first();
    $treeData = $tree ? json_decode($tree->tree_data, true) : [];

    // If no ID, show root (id = "1")
    if (!$id) {
        $currentNode = $treeData;
    } else {
        // Find node by ID inside the tree
        $currentNode = $this->findNodeById($treeData, $id);
    }

    return view('familyviews.familytree', compact('currentNode'));
}

    
    public function index()
    {
        $tree = DB::table('family_trees')->where('id', 1)->first();
        $treeData = $tree ? json_decode($tree->tree_data, true) : [];
        return response()->json($treeData);
    }

    private function generateFamilyTree($gen = 1, $maxGen = 6, $childrenPerParent = [7, 5, 5, 5, 5])
    {
    $createNode = function ($generation, $idPrefix) use (&$createNode, $maxGen, $childrenPerParent) {
        if ($generation > $maxGen) return [];
        $numChildren = $childrenPerParent[$generation - 1] ?? 0;
        $children = [];
        for ($i = 0; $i < $numChildren; $i++) {
            $children[] = [
                'id' => $idPrefix . '-' . ($i + 1),
                'name' => "Generation {$generation} - Child " . ($i + 1),
                'children' => $createNode($generation + 1, $idPrefix . '-' . ($i + 1)),
            ];
        }
        return $children;
    };

    return [
        'id' => '1',
        'name' => 'Generation 1 - Great Great Great Grandfather',
        'children' => $createNode(2, '1'),
    ];
    }

    public function getTree()
    {
        $members = FamilyMember::all();

        $buildTree = function ($parentId) use (&$buildTree, $members) {
            return $members->where('parent_id', $parentId)->map(function ($member) use ($buildTree) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'children' => $buildTree($member->id)
                ];
            })->values()->all();
        };

        $tree = $buildTree(null);

        return response()->json($tree[0] ?? null);
    }

    

    public function save(Request $request)
    {
        DB::table('family_trees')->updateOrInsert(
            ['id' => 1],
            ['tree_data' => json_encode($request->all())]
        );

        return response()->json(['success' => true]);
    }
}
