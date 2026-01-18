<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilyTreeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Core CRUD
Route::get('family-trees/{treeId}', [FamilyTreeController::class, 'getTree']);
Route::get('family-trees/{treeId}/members/{memberId}', [FamilyTreeController::class, 'getMember']);
Route::get('family-trees/{treeId}/members', [FamilyTreeController::class, 'listMembers']);
Route::post('family-trees', [FamilyTreeController::class, 'createTree']);
Route::post('family-trees/init', [FamilyTreeController::class, 'initTree']);
Route::put('family-trees/{treeId}/members/{memberId}', [FamilyTreeController::class, 'updateMemberComplete']);
Route::delete('family-trees/{treeId}', [FamilyTreeController::class, 'deleteTree']);

// Member Management - NEW ROUTES (RESTful)
Route::post('family-trees/{treeId}/members/{memberId}/children', [FamilyTreeController::class, 'addChild']);
Route::patch('family-trees/members/{memberId}/field', [FamilyTreeController::class, 'updateMemberField']);
Route::delete('family-trees/{treeId}/members/{memberId}', [FamilyTreeController::class, 'deleteMember']);

// Member Management - LEGACY ROUTES (Backward compatible)
Route::post('family-trees/members/add-child', [FamilyTreeController::class, 'addChild']);
Route::post('family-trees/members/update', [FamilyTreeController::class, 'updateMember']);
Route::post('family-trees/members/update-field', [FamilyTreeController::class, 'updateMemberField']);
Route::delete('family-trees/members/delete', [FamilyTreeController::class, 'deleteMember']);
Route::post('family-trees/save-tree', [FamilyTreeController::class, 'saveWholeTree']);
Route::put('family-trees/{treeId}/branch', [FamilyTreeController::class, 'saveWholeTree']);

// Relationships
Route::post('family-trees/{treeId}/members/{memberId}/spouse', [FamilyTreeController::class, 'addSpouse']);
Route::post('family-trees/{treeId}/members/{memberId}/parents', [FamilyTreeController::class, 'addParent']);

// Search & Query
Route::get('family-trees/{treeId}/search', [FamilyTreeController::class, 'searchMembers']);
Route::get('family-trees/{treeId}/members/{memberId}/ancestors', [FamilyTreeController::class, 'getAncestors']);
Route::get('family-trees/{treeId}/members/{memberId}/descendants', [FamilyTreeController::class, 'getDescendants']);
Route::get('family-trees/{treeId}/members/{memberId}/siblings', [FamilyTreeController::class, 'getSiblings']);
Route::get('family-trees/{treeId}/generation/{level}', [FamilyTreeController::class, 'getGeneration']);

// Bulk Operations
Route::post('family-trees/{treeId}/members/batch', [FamilyTreeController::class, 'batchCreateMembers']);
Route::put('family-trees/{treeId}/members/batch', [FamilyTreeController::class, 'batchUpdateMembers']);
Route::delete('family-trees/{treeId}/members/batch', [FamilyTreeController::class, 'batchDeleteMembers']);

// Import/Export
Route::get('family-trees/{treeId}/export', [FamilyTreeController::class, 'exportTree']);
Route::post('family-trees/{treeId}/import', [FamilyTreeController::class, 'importTree']);

// Statistics & Analytics
Route::get('family-trees/{treeId}/stats', [FamilyTreeController::class, 'getTreeStatistics']);
Route::get('family-trees/{treeId}/timeline', [FamilyTreeController::class, 'getTimeline']);

// Validation
Route::get('family-trees/{treeId}/validate', [FamilyTreeController::class, 'validateTree']);