<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FamilyMember;

class FamilyMemberSeeder extends Seeder
{
    public function run()
    {
        // Root ancestor
        $greatGrandparent = FamilyMember::create(['name' => 'Great Grandparent', 'parent_id' => null]);

        // Children
        $grandfather = FamilyMember::create(['name' => 'Grandfather', 'parent_id' => $greatGrandparent->id]);
        $grandmother = FamilyMember::create(['name' => 'Grandmother', 'parent_id' => $greatGrandparent->id]);

        // Next generation
        $father = FamilyMember::create(['name' => 'Father', 'parent_id' => $grandfather->id]);
        $uncle  = FamilyMember::create(['name' => 'Uncle', 'parent_id' => $grandfather->id]);

        // Father’s kids
        FamilyMember::create(['name' => 'You', 'parent_id' => $father->id]);
        FamilyMember::create(['name' => 'Your Sibling', 'parent_id' => $father->id]);

        // Uncle’s kids
        FamilyMember::create(['name' => 'Cousin 1', 'parent_id' => $uncle->id]);
        FamilyMember::create(['name' => 'Cousin 2', 'parent_id' => $uncle->id]);
    }

}
