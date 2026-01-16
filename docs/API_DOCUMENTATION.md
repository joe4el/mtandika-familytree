# Family Tree API Documentation

## Table of Contents
1. [Core CRUD Operations](#core-crud-operations)
2. [Member Management](#member-management)
3. [Relationship Management](#relationship-management)
4. [Search & Query](#search--query)
5. [Bulk Operations](#bulk-operations)
6. [Import/Export](#importexport)
7. [Statistics & Analytics](#statistics--analytics)
8. [Validation](#validation)
9. [Response Format](#response-format)
10. [Error Handling](#error-handling)

---

## Core CRUD Operations

### 1. Get Entire Tree
**GET** `/api/family-trees/{treeId}`

Retrieves the complete family tree structure.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "root",
    "name": "John Smith",
    "children": [...]
  },
  "meta": {
    "tree_id": 1,
    "created_at": "2024-01-01 00:00:00",
    "updated_at": "2024-01-15 12:30:00"
  }
}
```

---

### 2. Get Single Member
**GET** `/api/family-trees/{treeId}/members/{memberId}`

Retrieves detailed information about a specific member.

**Response:**
```json
{
  "success": true,
  "data": {
    "member": {
      "id": "root-1",
      "name": "Jane Smith",
      "dob": "1990-01-15",
      "dod": null,
      "bio": "Lorem ipsum...",
      "children": [...]
    },
    "parent_id": "root",
    "siblings": [...],
    "children_count": 3
  }
}
```

---

### 3. List All Members
**GET** `/api/family-trees/{treeId}/members`

Returns a flat list of all members in the tree.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "root",
      "name": "John Smith",
      "dob": "1960-05-20",
      "children": [...]
    },
    {
      "id": "root-1",
      "name": "Jane Smith",
      "dob": "1990-01-15",
      "children": [...]
    }
  ],
  "meta": {
    "total_count": 25
  }
}
```

---

### 4. Create New Tree
**POST** `/api/family-trees`

Creates a new family tree.

**Request Body:**
```json
{
  "name": "Smith Family Tree",
  "root_member_name": "John Smith"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Tree created successfully",
  "data": {
    "tree_id": 2,
    "tree_data": {
      "id": "root",
      "name": "John Smith",
      "children": []
    }
  }
}
```

---

### 5. Initialize Tree with Sample Data
**POST** `/api/family-trees/init`

Initializes a tree with sample data (useful for testing).

**Response:**
```json
{
  "success": true,
  "message": "Tree initialized",
  "data": {
    "id": "root",
    "name": "Generation 1 - Root",
    "children": [...]
  }
}
```

---

### 6. Update Member Completely
**PUT** `/api/family-trees/{treeId}/members/{memberId}`

Updates all fields of a member at once.

**Request Body:**
```json
{
  "name": "Jane Doe Smith",
  "dob": "1990-01-15",
  "dod": null,
  "bio": "Updated biography text...",
  "gender": "female",
  "occupation": "Software Engineer",
  "location": "New York, USA"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Member updated successfully",
  "data": {
    "id": "root-1",
    "name": "Jane Doe Smith",
    "dob": "1990-01-15",
    ...
  }
}
```

---

### 7. Delete Entire Tree
**DELETE** `/api/family-trees/{treeId}`

Permanently deletes a family tree.

**Response:**
```json
{
  "success": true,
  "message": "Tree deleted successfully"
}
```

---

## Member Management

### 8. Add Child Member
**POST** `/api/family-trees/{treeId}/members/{memberId}/children`

Adds a new child to a specific member.

**Request Body:**
```json
{
  "name": "New Child Name",
  "dob": "2020-05-15",
  "bio": "Optional biography"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Child added successfully",
  "data": {
    "id": "root-1-67890abc",
    "name": "New Child Name",
    "children": [],
    "dob": "2020-05-15",
    "dod": null,
    "bio": "Optional biography"
  }
}
```

**Legacy Route (backward compatibility):**
```
POST /api/family-trees/members/add-child
Body: { "parent_id": "root-1", "name": "New Child" }
```

---

### 9. Update Member Name
**POST** `/api/family-trees/members/update`

Updates a member's name (legacy route).

**Request Body:**
```json
{
  "id": "root-1",
  "name": "Updated Name"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Member name updated successfully"
}
```

---

### 10. Update Member Field
**PATCH** `/api/family-trees/members/{memberId}/field`

Updates a specific field of a member.

**Request Body:**
```json
{
  "field": "bio",
  "value": "This is the updated biography..."
}
```

**Valid Fields:**
- `name` - Member's name
- `dob` - Date of birth
- `dod` - Date of death
- `bio` - Biography text
- `gender` - Gender (male, female, other)
- `occupation` - Job/occupation
- `location` - Current location

**Response:**
```json
{
  "success": true,
  "message": "Bio updated successfully",
  "data": {
    "id": "root-1",
    "name": "Jane Smith",
    "bio": "This is the updated biography...",
    ...
  }
}
```

---

### 11. Delete Member
**DELETE** `/api/family-trees/{treeId}/members/{memberId}`

Deletes a member and all their descendants.

**Response:**
```json
{
  "success": true,
  "message": "Member deleted successfully"
}
```

**Note:** Cannot delete root node.

---

### 12. Save Whole Tree Branch
**PUT** `/api/family-trees/{treeId}/branch`

Updates an entire branch of the tree.

**Request Body:**
```json
{
  "id": "root-1",
  "tree": {
    "id": "root-1",
    "name": "Updated Member",
    "children": [...]
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Branch updated successfully"
}
```

---

## Relationship Management

### 13. Add Spouse
**POST** `/api/family-trees/{treeId}/members/{memberId}/spouse`

Adds spouse information to a member.

**Request Body:**
```json
{
  "name": "Spouse Name",
  "marriage_date": "2015-06-20"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Spouse added successfully",
  "data": {
    "name": "Spouse Name",
    "marriage_date": "2015-06-20",
    "added_at": "2024-01-16 10:30:00"
  }
}
```

---

### 14. Add Parent Information
**POST** `/api/family-trees/{treeId}/members/{memberId}/parents`

Adds parent information to a member.

**Request Body:**
```json
{
  "relationship": "father",
  "name": "Father Name"
}
```

**Valid Relationships:**
- `father`
- `mother`

**Response:**
```json
{
  "success": true,
  "message": "Parent information added successfully",
  "data": {
    "id": "root-1",
    "name": "Jane Smith",
    "parents": {
      "father": "Father Name"
    },
    ...
  }
}
```

---

## Search & Query

### 15. Search Members
**GET** `/api/family-trees/{treeId}/search?q={query}`

Searches for members by name (case-insensitive).

**Query Parameters:**
- `q` - Search query (minimum 2 characters)

**Example:**
```
GET /api/family-trees/1/search?q=john
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "root",
      "name": "John Smith",
      "dob": "1960-05-20",
      ...
    },
    {
      "id": "root-2-1",
      "name": "Johnny Smith Jr",
      "dob": "1995-03-10",
      ...
    }
  ],
  "meta": {
    "query": "john",
    "count": 2
  }
}
```

---

### 16. Get Ancestors
**GET** `/api/family-trees/{treeId}/members/{memberId}/ancestors`

Gets all ancestors of a member (from root to immediate parent).

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "root",
      "name": "John Smith (Great Grandfather)",
      ...
    },
    {
      "id": "root-1",
      "name": "Jane Smith (Grandfather)",
      ...
    },
    {
      "id": "root-1-2",
      "name": "Bob Smith (Father)",
      ...
    }
  ],
  "meta": {
    "count": 3
  }
}
```

---

### 17. Get Descendants
**GET** `/api/family-trees/{treeId}/members/{memberId}/descendants`

Gets all descendants of a member (children, grandchildren, etc.).

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "root-1-1",
      "name": "Child 1",
      ...
    },
    {
      "id": "root-1-2",
      "name": "Child 2",
      ...
    },
    {
      "id": "root-1-1-1",
      "name": "Grandchild 1",
      ...
    }
  ],
  "meta": {
    "count": 15
  }
}
```

---

### 18. Get Siblings
**GET** `/api/family-trees/{treeId}/members/{memberId}/siblings`

Gets all siblings of a member (sharing the same parent).

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "root-2",
      "name": "Sister Jane",
      ...
    },
    {
      "id": "root-3",
      "name": "Brother Bob",
      ...
    }
  ],
  "meta": {
    "count": 2
  }
}
```

---

### 19. Get Generation
**GET** `/api/family-trees/{treeId}/generation/{level}`

Gets all members at a specific generation level.

**Parameters:**
- `level` - Generation level (0 = root, 1 = children, 2 = grandchildren, etc.)

**Example:**
```
GET /api/family-trees/1/generation/2
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "root-1-1",
      "name": "Grandchild 1",
      ...
    },
    {
      "id": "root-1-2",
      "name": "Grandchild 2",
      ...
    }
  ],
  "meta": {
    "generation_level": 2,
    "count": 7
  }
}
```

---

## Bulk Operations

### 20. Batch Create Members
**POST** `/api/family-trees/{treeId}/members/batch`

Creates multiple members at once.

**Request Body:**
```json
{
  "members": [
    {
      "parent_id": "root",
      "name": "Child 1",
      "dob": "1990-01-15",
      "bio": "First child bio"
    },
    {
      "parent_id": "root",
      "name": "Child 2",
      "dob": "1992-05-20"
    },
    {
      "parent_id": "root-1",
      "name": "Grandchild 1",
      "dob": "2015-08-10"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "3 members created successfully",
  "data": {
    "created": [
      { "id": "root-abc123", "name": "Child 1", ... },
      { "id": "root-def456", "name": "Child 2", ... },
      { "id": "root-1-ghi789", "name": "Grandchild 1", ... }
    ],
    "errors": []
  }
}
```

---

### 21. Batch Update Members
**PUT** `/api/family-trees/{treeId}/members/batch`

Updates multiple members at once.

**Request Body:**
```json
{
  "updates": [
    {
      "id": "root-1",
      "fields": {
        "name": "Updated Name",
        "bio": "Updated biography"
      }
    },
    {
      "id": "root-2",
      "fields": {
        "dob": "1995-03-15",
        "occupation": "Engineer"
      }
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "2 members updated successfully",
  "data": {
    "updated_count": 2,
    "updated_ids": ["root-1", "root-2"],
    "errors": []
  }
}
```

---

### 22. Batch Delete Members
**DELETE** `/api/family-trees/{treeId}/members/batch`

Deletes multiple members at once.

**Request Body:**
```json
{
  "member_ids": ["root-1-2", "root-1-3", "root-2-1"]
}
```

**Response:**
```json
{
  "success": true,
  "message": "3 members deleted successfully",
  "data": {
    "deleted_count": 3,
    "deleted_ids": ["root-1-2", "root-1-3", "root-2-1"],
    "errors": []
  }
}
```

---

## Import/Export

### 23. Export Tree
**GET** `/api/family-trees/{treeId}/export?format={format}`

Exports the tree in various formats.

**Query Parameters:**
- `format` - Export format (`json` or `csv`)

**JSON Format Example:**
```
GET /api/family-trees/1/export?format=json
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "root",
    "name": "John Smith",
    "children": [...]
  },
  "exported_at": "2024-01-16 12:30:00"
}
```

**CSV Format Example:**
```
GET /api/family-trees/1/export?format=csv
```

**Response:** CSV file download with headers:
```
ID,Name,DOB,DOD,Bio,Parent ID
root,John Smith,1960-05-20,,,
root-1,Jane Smith,1990-01-15,,Biography text,root
...
```

---

### 24. Import Tree
**POST** `/api/family-trees/{treeId}/import`

Imports tree data from JSON.

**Request Body:**
```json
{
  "tree_data": {
    "id": "imported-root",
    "name": "Imported Member",
    "children": [...]
  },
  "merge": false
}
```

**Parameters:**
- `merge` - If `true`, appends to existing tree. If `false`, replaces entirely.

**Response:**
```json
{
  "success": true,
  "message": "Tree imported successfully",
  "data": {
    "id": "imported-root",
    "name": "Imported Member",
    "children": [...]
  }
}
```

---

## Statistics & Analytics

### 25. Get Tree Statistics
**GET** `/api/family-trees/{treeId}/stats`

Gets comprehensive statistics about the tree.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_members": 47,
    "total_generations": 5,
    "members_with_bio": 32,
    "members_with_dob": 45,
    "members_with_dod": 12,
    "deceased_count": 12,
    "living_count": 35,
    "male_count": 23,
    "female_count": 24,
    "oldest_member": {
      "id": "root",
      "name": "John Smith",
      "dob": "1920-01-15",
      ...
    },
    "youngest_member": {
      "id": "root-3-2-1",
      "name": "Baby Smith",
      "dob": "2023-08-20",
      ...
    }
  }
}
```

---

### 26. Get Timeline
**GET** `/api/family-trees/{treeId}/timeline`

Gets a chronological timeline of birth and death events.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "type": "birth",
      "date": "1920-01-15",
      "member_id": "root",
      "member_name": "John Smith",
      "description": "John Smith was born"
    },
    {
      "type": "birth",
      "date": "1950-06-20",
      "member_id": "root-1",
      "member_name": "Jane Smith",
      "description": "Jane Smith was born"
    },
    {
      "type": "death",
      "date": "2010-12-31",
      "member_id": "root",
      "member_name": "John Smith",
      "description": "John Smith passed away"
    }
  ],
  "meta": {
    "total_events": 94
  }
}
```

---

## Validation

### 27. Validate Tree
**GET** `/api/family-trees/{treeId}/validate`

Checks the tree for data integrity issues.

**Response:**
```json
{
  "success": true,
  "data": {
    "is_valid": false,
    "issues": [
      {
        "severity": "error",
        "type": "invalid_dates",
        "message": "Date of birth is after date of death",
        "member_id": "root-2",
        "member_name": "Bob Smith"
      },
      {
        "severity": "warning",
        "type": "missing_name",
        "message": "Member without name",
        "member_id": "root-3-1"
      }
    ],
    "total_issues": 5,
    "critical_count": 0,
    "error_count": 3,
    "warning_count": 2
  }
}
```

**Issue Types:**
- `duplicate_ids` - Multiple members with same ID (critical)
- `invalid_dates` - DOB after DOD (error)
- `orphaned_node` - Member with no parent (error)
- `missing_name` - Member without name (warning)

---

## Response Format

All API responses follow this standard format:

### Success Response:
```json
{
  "success": true,
  "message": "Optional success message",
  "data": { ... },
  "meta": { ... }
}
```

### Error Response:
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

---

## Error Handling

### HTTP Status Codes

| Code | Meaning | When Used |
|------|---------|-----------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request (e.g., trying to delete root) |
| 404 | Not Found | Tree or member not found |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Server Error | Internal server error |

### Validation Errors

When validation fails (422), the response includes detailed error information:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "dob": ["The dob must be a valid date."]
  }
}
```

---

## Usage Examples

### Example 1: Create a Complete Family

```javascript
// 1. Create the tree
const createResponse = await fetch('/api/family-trees', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'Smith Family',
    root_member_name: 'John Smith'
  })
});
const { data: { tree_id } } = await createResponse.json();

// 2. Add children
await fetch(`/api/family-trees/${tree_id}/members/root/children`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'Jane Smith',
    dob: '1990-05-15'
  })
});

// 3. Update member details
await fetch(`/api/family-trees/${tree_id}/members/root`, {
  method: 'PUT',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    dob: '1960-01-01',
    bio: 'Founder of Smith family...',
    occupation: 'Farmer'
  })
});
```

### Example 2: Search and Display Results

```javascript
// Search for members
const searchResponse = await fetch('/api/family-trees/1/search?q=john');
const { data: members } = await searchResponse.json();

// Display search results
members.forEach(member => {
  console.log(`${member.name} (${member.id})`);
  console.log(`Born: ${member.dob || 'Unknown'}`);
  console.log('---');
});
```

### Example 3: Generate Family Report

```javascript
// Get statistics
const statsResponse = await fetch('/api/family-trees/1/stats');
const { data: stats } = await statsResponse.json();

console.log(`Total Members: ${stats.total_members}`);
console.log(`Generations: ${stats.total_generations}`);
console.log(`Living: ${stats.living_count}`);
console.log(`Deceased: ${stats.deceased_count}`);

// Get timeline
const timelineResponse = await fetch('/api/family-trees/1/timeline');
const { data: events } = await timelineResponse.json();

console.log('\nFamily Timeline:');
events.forEach(event => {
  console.log(`${event.date}: ${event.description}`);
});
```

### Example 4: Validate Before Export

```javascript
// Validate tree
const validateResponse = await fetch('/api/family-trees/1/validate');
const { data: validation } = await validateResponse.json();

if (validation.is_valid) {
  // Export to CSV
  window.location.href = '/api/family-trees/1/export?format=csv';
} else {
  console.error('Tree has issues:', validation.issues);
  alert(`Please fix ${validation.total_issues} issues before exporting`);
}
```

---

## Best Practices

1. **Always validate before bulk operations** - Use the validate endpoint before making large changes

2. **Use batch operations for efficiency** - When adding/updating multiple members, use batch endpoints

3. **Handle errors gracefully** - Check `success` field and display appropriate messages

4. **Implement pagination** - For large trees, consider implementing client-side pagination when displaying flat lists

5. **Backup before imports** - Export existing tree before importing new data

6. **Use appropriate HTTP methods** - GET for reading, POST for creating, PUT for updating, DELETE for deleting

7. **Validate dates** - Ensure DOB is before DOD

8. **Search optimization** - For better search results, maintain consistent naming conventions

---

## Support & Contact

For issues or questions about the API:
- Check validation endpoint for data integrity issues
- Review error messages in response
- Ensure all required fields are provided
- Verify API routes are correctly registered

---

**Last Updated:** January 2025
**API Version:** 1.0
