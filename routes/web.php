<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilyTreeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect home to family tree
Route::get('/', function () {
    return redirect('/family-tree');
});

// Family Tree Web Pages (HTML views)
Route::get('/family-tree/{id?}', [FamilyTreeController::class, 'showPage'])->name('family.tree');
Route::get('/family-tree-view/{id?}', [FamilyTreeController::class, 'show'])->name('family.tree.view');
Route::get('/family/bio/{id}', [FamilyTreeController::class, 'showBio'])->name('family.bio');
Route::post('/family-tree/save', [FamilyTreeController::class, 'saveWholeTree'])->name('familytree.save');

Route::post('/family-tree/save', [FamilyTreeController::class, 'saveWholeTree'])->name('familytree.save');
Route::post('/family-tree/add-child', [FamilyTreeController::class, 'addChild'])->name('familytree.addChild');
Route::post('/family-tree/update-member', [FamilyTreeController::class, 'updateMember'])->name('familytree.updateMember');
Route::post('/family-tree/update-field', [FamilyTreeController::class, 'updateMemberField'])->name('familytree.updateField');
Route::delete('/family-tree/delete-member', [FamilyTreeController::class, 'deleteMember'])->name('familytree.deleteMember');

// Optional: Simple test route
Route::get('/test', function () {
    return view('welcome'); // Shows Laravel welcome page
});