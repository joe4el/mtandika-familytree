<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilyTreeController;

Route::get('/', function () {
return view('familyviews.familytree', ['treeData' => $treeData]);
});

// Use controller to fetch and pass family members to the view
Route::get('/', [FamilyTreeController::class, 'showPage']);
Route::get('/family-tree', [FamilyTreeController::class, 'showPage']); 
Route::get('/get-tree',     [FamilyTreeController::class, 'index']);  
Route::post('/add-member',  [FamilyTreeController::class, 'add']);
Route::post('/save-tree',   [FamilyTreeController::class, 'save']);