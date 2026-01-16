<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilyTreeController;

/*
|--------------------------------------------------------------------------
| Family Tree API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api and return JSON responses
|
*/

// ==========================================
// CORE CRUD OPERATIONS
// ==========================================

// Get entire tree
Route::get('/family-trees/{treeId}', [FamilyTreeController::class, 'getTree']);

// Get single member details
Route::get('/family-trees/{treeId}/members/{memberId}', [FamilyTreeController::class, 'getMember']);

// List all members (flat structure)
Route::get('/family-trees/{treeId}/members', [FamilyTreeController::class, 'listMembers']);

// Create new tree
Route::post('/family-trees', [FamilyTreeController::class, 'createTree']);

// Initialize tree with sample data
Route::post('/family-trees/init', [FamilyTreeController::class, 'initTree']);

// Update member completely
Route::put('/family-trees/{treeId}/members/{memberId}', [FamilyTreeController::class, 'updateMemberComplete']);

// Delete entire tree
Route::delete('/family-trees/{treeId}', [FamilyTreeController::class, 'deleteTree']);

// ==========================================
// MEMBER MANAGEMENT (Legacy & New)
// ==========================================

// Add child to a member
Route::post('/family-trees/{treeId}/members/{memberId}/children', [FamilyTreeController::class, 'addChild']);

// Legacy route support for backward compatibility
Route::post('/family-trees/members/add-child', [FamilyTreeController::class, 'addChild']);

// Update member name (legacy)
Route::post('/family-trees/members/update', [FamilyTreeController::class, 'updateMember']);

// Update specific member field
Route::patch('/family-trees/members/{memberId}/field', [FamilyTreeController::class, 'updateMemberField']);
Route::post('/family-trees/members/update-field', [FamilyTreeController::class, 'updateMemberField']); // Legacy

// Delete member
Route::delete('/family-trees/{treeId}/members/{memberId}', [FamilyTreeController::class, 'deleteMember']);
Route::delete('/family-trees/members/delete', [FamilyTreeController::class, 'deleteMember']); // Legacy

// Save whole tree branch
Route::put('/family-trees/{treeId}/branch', [FamilyTreeController::class, 'saveWholeTree']);
Route::post('/family-trees/save-tree', [FamilyTreeController::class, 'saveWholeTree']); // Legacy

// ==========================================
// RELATIONSHIP MANAGEMENT
// ==========================================

// Add spouse to member
Route::post('/family-trees/{treeId}/members/{memberId}/spouse', [FamilyTreeController::class, 'addSpouse']);

// Add parent information to member
Route::post('/family-trees/{treeId}/members/{memberId}/parents', [FamilyTreeController::class, 'addParent']);

// ==========================================
// SEARCH & QUERY OPERATIONS
// ==========================================

// Search members by name
Route::get('/family-trees/{treeId}/search', [FamilyTreeController::class, 'searchMembers']);

// Get all ancestors of a member
Route::get('/family-trees/{treeId}/members/{memberId}/ancestors', [FamilyTreeController::class, 'getAncestors']);

// Get all descendants of a member
Route::get('/family-trees/{treeId}/members/{memberId}/descendants', [FamilyTreeController::class, 'getDescendants']);

// Get siblings of a member
Route::get('/family-trees/{treeId}/members/{memberId}/siblings', [FamilyTreeController::class, 'getSiblings']);

// Get all members in a specific generation
Route::get('/family-trees/{treeId}/generation/{level}', [FamilyTreeController::class, 'getGeneration']);

// ==========================================
// BULK OPERATIONS
// ==========================================

// Batch create members
Route::post('/family-trees/{treeId}/members/batch', [FamilyTreeController::class, 'batchCreateMembers']);

// Batch update members
Route::put('/family-trees/{treeId}/members/batch', [FamilyTreeController::class, 'batchUpdateMembers']);

// Batch delete members
Route::delete('/family-trees/{treeId}/members/batch', [FamilyTreeController::class, 'batchDeleteMembers']);

// ==========================================
// IMPORT/EXPORT
// ==========================================

// Export tree in various formats (json, csv)
Route::get('/family-trees/{treeId}/export', [FamilyTreeController::class, 'exportTree']);

// Import tree from JSON
Route::post('/family-trees/{treeId}/import', [FamilyTreeController::class, 'importTree']);

// ==========================================
// STATISTICS & ANALYTICS
// ==========================================

// Get tree statistics
Route::get('/family-trees/{treeId}/stats', [FamilyTreeController::class, 'getTreeStatistics']);

// Get timeline of events
Route::get('/family-trees/{treeId}/timeline', [FamilyTreeController::class, 'getTimeline']);

// ==========================================
// VALIDATION & INTEGRITY
// ==========================================

// Validate tree for issues
Route::get('/family-trees/{treeId}/validate', [FamilyTreeController::class, 'validateTree']);

// ==========================================
// WEB ROUTES (Views)
// ==========================================

// These should be in your web.php routes file
/*
Route::get('/family-tree/{id?}', [FamilyTreeController::class, 'showPage'])->name('family.tree');
Route::get('/family-tree-view/{id?}', [FamilyTreeController::class, 'show'])->name('family.tree.view');
Route::get('/family/bio/{id}', [FamilyTreeController::class, 'showBio'])->name('family.bio');
*/
