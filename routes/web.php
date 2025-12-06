<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilyTreeController;

Route::get('/', [FamilyTreeController::class, 'showPage'])->name('familytree.show');

Route::get('/family-tree/{id?}', [FamilyTreeController::class, 'showPage'])->name('familytree.show');
Route::post('/family-tree/add-child', [FamilyTreeController::class, 'addChild'])->name('familytree.add');
Route::post('/family-tree/update', [FamilyTreeController::class, 'updateMember'])->name('familytree.update');
Route::post('/delete-member', [FamilyTreeController::class, 'deleteMember'])->name('delete.member');
Route::post('/family-tree/save', [FamilyTreeController::class, 'saveWholeTree'])->name('familytree.save');
Route::get('/generate-family-tree', [FamilyTreeController::class, 'generateInitialFamilyTree']);
Route::get('/init-tree', [FamilyTreeController::class, 'initTree']);
Route::get('/familytree/bio/{id}', [FamilyTreeController::class, 'showBio'])->name('familytree.bio');
Route::post('/family-tree/update-field/{id}', [FamilyTreeController::class, 'updateField']);
Route::post('/update-member-field', [FamilyTreeController::class, 'updateMemberField']);
