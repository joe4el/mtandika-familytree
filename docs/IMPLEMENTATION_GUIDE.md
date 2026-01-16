# Family Tree API Implementation Guide

## Quick Start

This guide will help you implement all the new APIs in your Laravel project.

---

## Step 1: Update Your Controller

Replace your existing `app/Http/Controllers/FamilyTreeController.php` with the new enhanced version:

**Location:** `app/Http/Controllers/FamilyTreeController.php`

The new controller includes:
- ✅ All original functionality (backward compatible)
- ✅ 27+ new API endpoints
- ✅ Proper validation
- ✅ Consistent error handling
- ✅ RESTful structure

---

## Step 2: Register API Routes

Add the new routes to your `routes/api.php` file:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilyTreeController;

// Core CRUD
Route::get('/family-trees/{treeId}', [FamilyTreeController::class, 'getTree']);
Route::get('/family-trees/{treeId}/members/{memberId}', [FamilyTreeController::class, 'getMember']);
Route::get('/family-trees/{treeId}/members', [FamilyTreeController::class, 'listMembers']);
Route::post('/family-trees', [FamilyTreeController::class, 'createTree']);
Route::post('/family-trees/init', [FamilyTreeController::class, 'initTree']);
Route::put('/family-trees/{treeId}/members/{memberId}', [FamilyTreeController::class, 'updateMemberComplete']);
Route::delete('/family-trees/{treeId}', [FamilyTreeController::class, 'deleteTree']);

// Member Management
Route::post('/family-trees/{treeId}/members/{memberId}/children', [FamilyTreeController::class, 'addChild']);
Route::post('/family-trees/members/add-child', [FamilyTreeController::class, 'addChild']); // Legacy
Route::post('/family-trees/members/update', [FamilyTreeController::class, 'updateMember']); // Legacy
Route::patch('/family-trees/members/{memberId}/field', [FamilyTreeController::class, 'updateMemberField']);
Route::post('/family-trees/members/update-field', [FamilyTreeController::class, 'updateMemberField']); // Legacy
Route::delete('/family-trees/{treeId}/members/{memberId}', [FamilyTreeController::class, 'deleteMember']);
Route::delete('/family-trees/members/delete', [FamilyTreeController::class, 'deleteMember']); // Legacy
Route::put('/family-trees/{treeId}/branch', [FamilyTreeController::class, 'saveWholeTree']);
Route::post('/family-trees/save-tree', [FamilyTreeController::class, 'saveWholeTree']); // Legacy

// Relationships
Route::post('/family-trees/{treeId}/members/{memberId}/spouse', [FamilyTreeController::class, 'addSpouse']);
Route::post('/family-trees/{treeId}/members/{memberId}/parents', [FamilyTreeController::class, 'addParent']);

// Search & Query
Route::get('/family-trees/{treeId}/search', [FamilyTreeController::class, 'searchMembers']);
Route::get('/family-trees/{treeId}/members/{memberId}/ancestors', [FamilyTreeController::class, 'getAncestors']);
Route::get('/family-trees/{treeId}/members/{memberId}/descendants', [FamilyTreeController::class, 'getDescendants']);
Route::get('/family-trees/{treeId}/members/{memberId}/siblings', [FamilyTreeController::class, 'getSiblings']);
Route::get('/family-trees/{treeId}/generation/{level}', [FamilyTreeController::class, 'getGeneration']);

// Bulk Operations
Route::post('/family-trees/{treeId}/members/batch', [FamilyTreeController::class, 'batchCreateMembers']);
Route::put('/family-trees/{treeId}/members/batch', [FamilyTreeController::class, 'batchUpdateMembers']);
Route::delete('/family-trees/{treeId}/members/batch', [FamilyTreeController::class, 'batchDeleteMembers']);

// Import/Export
Route::get('/family-trees/{treeId}/export', [FamilyTreeController::class, 'exportTree']);
Route::post('/family-trees/{treeId}/import', [FamilyTreeController::class, 'importTree']);

// Statistics & Analytics
Route::get('/family-trees/{treeId}/stats', [FamilyTreeController::class, 'getTreeStatistics']);
Route::get('/family-trees/{treeId}/timeline', [FamilyTreeController::class, 'getTimeline']);

// Validation
Route::get('/family-trees/{treeId}/validate', [FamilyTreeController::class, 'validateTree']);
```

**Pro Tip:** You can organize these routes into groups for better maintainability:

```php
Route::prefix('family-trees')->group(function () {
    // Tree operations
    Route::get('/', [FamilyTreeController::class, 'listTrees']);
    Route::get('/{treeId}', [FamilyTreeController::class, 'getTree']);
    Route::post('/', [FamilyTreeController::class, 'createTree']);
    Route::delete('/{treeId}', [FamilyTreeController::class, 'deleteTree']);
    
    // Member operations
    Route::prefix('{treeId}')->group(function () {
        Route::get('/members', [FamilyTreeController::class, 'listMembers']);
        Route::get('/members/{memberId}', [FamilyTreeController::class, 'getMember']);
        // ... etc
    });
});
```

---

## Step 3: Update Database Schema (Optional)

If you want to add more fields to family members, update your migration:

```php
Schema::create('family_trees', function (Blueprint $table) {
    $table->id();
    $table->json('tree_data'); // Main tree structure
    $table->timestamps();
});
```

The tree_data JSON structure supports these fields per member:
- `id` (string, required)
- `name` (string, required)
- `dob` (date, optional) - Date of birth
- `dod` (date, optional) - Date of death
- `bio` (text, optional) - Biography
- `gender` (string, optional) - male/female/other
- `occupation` (string, optional)
- `location` (string, optional)
- `spouses` (array, optional) - List of spouse information
- `parents` (object, optional) - Father/mother information
- `children` (array, required) - Child nodes

---

## Step 4: Test Your APIs

### Option A: Using Postman

1. Import the provided Postman collection: `FamilyTree_Postman_Collection.json`
2. Update the `base_url` variable to your local/production URL
3. Start testing each endpoint

### Option B: Using cURL

```bash
# Initialize tree with sample data
curl -X POST http://localhost:8000/api/family-trees/init

# Get entire tree
curl http://localhost:8000/api/family-trees/1

# Search for members
curl "http://localhost:8000/api/family-trees/1/search?q=smith"

# Get statistics
curl http://localhost:8000/api/family-trees/1/stats

# Add a child
curl -X POST http://localhost:8000/api/family-trees/1/members/root/children \
  -H "Content-Type: application/json" \
  -d '{"name": "New Child", "dob": "2020-01-01"}'
```

### Option C: JavaScript/Axios Example

```javascript
// Initialize axios with base URL
const api = axios.create({
  baseURL: 'http://localhost:8000/api'
});

// Get tree
const tree = await api.get('/family-trees/1');
console.log(tree.data);

// Search members
const results = await api.get('/family-trees/1/search', {
  params: { q: 'john' }
});

// Add child
const newChild = await api.post('/family-trees/1/members/root/children', {
  name: 'New Child',
  dob: '2020-01-01'
});

// Get statistics
const stats = await api.get('/family-trees/1/stats');
console.log(`Total members: ${stats.data.data.total_members}`);
```

---

## Step 5: Frontend Integration Examples

### React Example

```jsx
import { useState, useEffect } from 'react';
import axios from 'axios';

function FamilyTreeComponent() {
  const [tree, setTree] = useState(null);
  const [stats, setStats] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [searchResults, setSearchResults] = useState([]);

  useEffect(() => {
    loadTree();
    loadStats();
  }, []);

  const loadTree = async () => {
    const response = await axios.get('/api/family-trees/1');
    setTree(response.data.data);
  };

  const loadStats = async () => {
    const response = await axios.get('/api/family-trees/1/stats');
    setStats(response.data.data);
  };

  const handleSearch = async (e) => {
    e.preventDefault();
    if (searchQuery.length >= 2) {
      const response = await axios.get(`/api/family-trees/1/search?q=${searchQuery}`);
      setSearchResults(response.data.data);
    }
  };

  const addChild = async (parentId) => {
    const name = prompt('Enter child name:');
    if (name) {
      await axios.post(`/api/family-trees/1/members/${parentId}/children`, { name });
      loadTree(); // Reload tree
    }
  };

  return (
    <div>
      <h1>Family Tree</h1>
      
      {/* Statistics */}
      {stats && (
        <div className="stats">
          <p>Total Members: {stats.total_members}</p>
          <p>Generations: {stats.total_generations}</p>
          <p>Living: {stats.living_count}</p>
        </div>
      )}

      {/* Search */}
      <form onSubmit={handleSearch}>
        <input
          type="text"
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          placeholder="Search members..."
        />
        <button type="submit">Search</button>
      </form>

      {/* Search Results */}
      {searchResults.length > 0 && (
        <div className="search-results">
          <h3>Search Results:</h3>
          {searchResults.map(member => (
            <div key={member.id}>
              {member.name} ({member.dob || 'Unknown'})
            </div>
          ))}
        </div>
      )}

      {/* Tree Display */}
      {tree && (
        <div className="tree">
          {/* Render your tree here */}
          <TreeNode node={tree} onAddChild={addChild} />
        </div>
      )}
    </div>
  );
}
```

### Vue Example

```vue
<template>
  <div class="family-tree">
    <h1>Family Tree</h1>

    <!-- Statistics -->
    <div v-if="stats" class="stats">
      <p>Total Members: {{ stats.total_members }}</p>
      <p>Generations: {{ stats.total_generations }}</p>
      <p>Living: {{ stats.living_count }}</p>
    </div>

    <!-- Search -->
    <form @submit.prevent="search">
      <input 
        v-model="searchQuery" 
        type="text" 
        placeholder="Search members..."
      />
      <button type="submit">Search</button>
    </form>

    <!-- Search Results -->
    <div v-if="searchResults.length" class="search-results">
      <h3>Search Results:</h3>
      <div v-for="member in searchResults" :key="member.id">
        {{ member.name }} ({{ member.dob || 'Unknown' }})
      </div>
    </div>

    <!-- Tree -->
    <tree-node 
      v-if="tree" 
      :node="tree" 
      @add-child="addChild"
    />
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      tree: null,
      stats: null,
      searchQuery: '',
      searchResults: []
    };
  },

  mounted() {
    this.loadTree();
    this.loadStats();
  },

  methods: {
    async loadTree() {
      const response = await axios.get('/api/family-trees/1');
      this.tree = response.data.data;
    },

    async loadStats() {
      const response = await axios.get('/api/family-trees/1/stats');
      this.stats = response.data.data;
    },

    async search() {
      if (this.searchQuery.length >= 2) {
        const response = await axios.get(`/api/family-trees/1/search`, {
          params: { q: this.searchQuery }
        });
        this.searchResults = response.data.data;
      }
    },

    async addChild(parentId) {
      const name = prompt('Enter child name:');
      if (name) {
        await axios.post(`/api/family-trees/1/members/${parentId}/children`, { 
          name 
        });
        await this.loadTree();
      }
    }
  }
};
</script>
```

---

## Step 6: Error Handling Best Practices

### Backend (Already Implemented)

The controller returns consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

### Frontend Example

```javascript
try {
  const response = await axios.post('/api/family-trees/1/members/root/children', {
    name: 'New Child',
    dob: 'invalid-date'
  });
  
  if (response.data.success) {
    console.log('Success:', response.data.message);
  }
} catch (error) {
  if (error.response) {
    // Server responded with error
    const { status, data } = error.response;
    
    if (status === 422) {
      // Validation errors
      console.error('Validation errors:', data.errors);
      // Display each validation error to user
      Object.keys(data.errors).forEach(field => {
        alert(`${field}: ${data.errors[field][0]}`);
      });
    } else if (status === 404) {
      alert('Resource not found');
    } else {
      alert(data.message || 'An error occurred');
    }
  } else {
    // Network error
    alert('Network error. Please check your connection.');
  }
}
```

---

## Step 7: Performance Optimization

### Enable Caching (Optional)

For frequently accessed data like statistics:

```php
use Illuminate\Support\Facades\Cache;

public function getTreeStatistics($treeId)
{
    $cacheKey = "tree_stats_{$treeId}";
    
    return Cache::remember($cacheKey, 3600, function () use ($treeId) {
        // ... existing statistics logic
    });
}

// Clear cache when tree is updated
private function saveTreeToDbById($treeId, $tree)
{
    DB::table('family_trees')->where('id', $treeId)->update([
        'tree_data' => json_encode($tree),
        'updated_at' => now()
    ]);
    
    // Clear cache
    Cache::forget("tree_stats_{$treeId}");
}
```

### Add Pagination (For Large Trees)

```php
public function listMembers(Request $request, $treeId)
{
    $perPage = $request->input('per_page', 50);
    $page = $request->input('page', 1);
    
    $tree = $this->getTreeById($treeId);
    if (!$tree) {
        return response()->json(['success' => false, 'message' => 'Tree not found'], 404);
    }

    $members = [];
    $this->flattenTree($tree, $members);
    
    // Paginate
    $total = count($members);
    $offset = ($page - 1) * $perPage;
    $paginatedMembers = array_slice($members, $offset, $perPage);

    return response()->json([
        'success' => true,
        'data' => $paginatedMembers,
        'meta' => [
            'total_count' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage)
        ]
    ]);
}
```

---

## Step 8: Security Considerations

### Add Authentication (Recommended)

Protect your API routes:

```php
// In routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/family-trees/{treeId}', [FamilyTreeController::class, 'getTree']);
    // ... other routes
});
```

### Add Authorization

Check if user owns the tree:

```php
public function getTree($treeId)
{
    $tree = DB::table('family_trees')
        ->where('id', $treeId)
        ->where('user_id', auth()->id()) // Add user_id column
        ->first();
    
    if (!$tree) {
        return response()->json([
            'success' => false,
            'message' => 'Tree not found or access denied'
        ], 404);
    }
    
    // ... rest of the method
}
```

### Rate Limiting

Laravel provides built-in rate limiting:

```php
// In app/Providers/RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

---

## Step 9: API Versioning (Optional)

For future-proofing your API:

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::get('/family-trees/{treeId}', [FamilyTreeController::class, 'getTree']);
    // ... all routes
});

// Access via: /api/v1/family-trees/1
```

---

## Step 10: Documentation & Testing

### API Documentation Tools

**Option 1: Laravel API Documentation Generator**
```bash
composer require mpociot/laravel-apidoc-generator
php artisan apidoc:generate
```

**Option 2: Swagger/OpenAPI**
```bash
composer require "darkaonline/l5-swagger"
php artisan l5-swagger:generate
```

### Unit Testing Example

```php
// tests/Feature/FamilyTreeApiTest.php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FamilyTreeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_tree()
    {
        $response = $this->getJson('/api/family-trees/1');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'children'],
                     'meta'
                 ]);
    }

    public function test_can_search_members()
    {
        $response = $this->getJson('/api/family-trees/1/search?q=john');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data',
                     'meta' => ['query', 'count']
                 ]);
    }

    public function test_can_add_child()
    {
        $response = $this->postJson('/api/family-trees/1/members/root/children', [
            'name' => 'Test Child'
        ]);
        
        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Child added successfully'
                 ]);
    }
}
```

Run tests:
```bash
php artisan test --filter FamilyTreeApiTest
```

---

## Troubleshooting

### Common Issues

**1. 404 Not Found**
- Check routes are registered: `php artisan route:list`
- Verify base URL is correct
- Ensure API routes are in `routes/api.php`

**2. 500 Internal Server Error**
- Check Laravel logs: `storage/logs/laravel.log`
- Verify database connection
- Ensure `tree_data` column exists and is JSON type

**3. CORS Issues (Frontend)**
Add to `config/cors.php`:
```php
'paths' => ['api/*'],
'allowed_origins' => ['http://localhost:3000'], // Your frontend URL
```

**4. Validation Errors**
Check request body format matches documentation

**5. Database Query Issues**
Clear cache: `php artisan cache:clear`

---

## Next Steps

1. ✅ Implement the controller and routes
2. ✅ Test with Postman collection
3. ✅ Add authentication if needed
4. ✅ Integrate with your frontend
5. ✅ Add monitoring and logging
6. ✅ Deploy to production

---

## Support Resources

- **API Documentation:** See `API_DOCUMENTATION.md`
- **Postman Collection:** Import `FamilyTree_Postman_Collection.json`
- **Laravel Docs:** https://laravel.com/docs
- **Testing Guide:** Run `php artisan test`

---

## Changelog

### Version 1.0 (Current)
- ✅ 27+ API endpoints
- ✅ RESTful structure
- ✅ Comprehensive validation
- ✅ Search & query operations
- ✅ Bulk operations
- ✅ Import/export functionality
- ✅ Statistics & analytics
- ✅ Tree validation
- ✅ Backward compatibility with legacy routes

---

**Need Help?** Check the API documentation or test with the provided Postman collection!
