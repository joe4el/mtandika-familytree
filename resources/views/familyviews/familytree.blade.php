<!DOCTYPE html>
<html>
<head>
    <title>Family Tree</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        
        .navigation {
            margin: 20px 0;
        }
        
        .nav-btn {
            display: inline-block;
            background: #2196F3;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin-right: 10px;
            border: none;
            cursor: pointer;
        }
        
        .nav-btn:hover {
            background: #1976D2;
        }
        
        .nav-btn.home {
            background: #607D8B;
        }
        
        .nav-btn.home:hover {
            background: #455A64;
        }
        
        .member-card {
            background: white;
            border: 2px solid #333;
            padding: 30px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .member-card h2 {
            margin-top: 0;
            color: #2196F3;
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .member-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #2196F3;
        }
        
        .info-row {
            display: flex;
            margin: 10px 0;
            align-items: center;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .info-value.empty {
            color: #999;
            font-style: italic;
        }
        
        .bio-section {
            margin: 20px 0;
        }
        
        .bio-section h3 {
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }
        
        .bio-text {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        
        .bio-text.empty {
            color: #999;
            font-style: italic;
        }
        
        .actions {
            margin: 20px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        
        .btn:hover {
            background: #45a049;
            transform: translateY(-1px);
        }
        
        .btn.edit {
            background: #FF9800;
        }
        
        .btn.edit:hover {
            background: #F57C00;
        }
        
        .btn.delete {
            background: #f44336;
        }
        
        .btn.delete:hover {
            background: #da190b;
        }
        
        .btn.secondary {
            background: #9C27B0;
        }
        
        .btn.secondary:hover {
            background: #7B1FA2;
        }
        
        .children-section {
            margin-top: 30px;
        }
        
        .children-section h3 {
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }
        
        ul {
            list-style: none;
            padding: 0;
        }
        
        ul li {
            padding: 15px;
            margin: 10px 0;
            background: #f9f9f9;
            border-left: 4px solid #4CAF50;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        ul li:hover {
            background: #e8f5e9;
            transform: translateX(5px);
        }
        
        ul li a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            font-size: 16px;
        }
        
        .empty-state {
            padding: 20px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            color: #856404;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            color: #666;
        }
        
        .footer a {
            color: #2196F3;
            text-decoration: none;
            margin: 0 10px;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
        }
        
        .modal-header h2 {
            margin: 0;
            color: #333;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin: 15px 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
        
        .modal-actions {
            margin-top: 20px;
            text-align: right;
        }
        
        .modal-actions button {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <h1>🌳 Family Tree</h1>
    
    <!-- Navigation / Back Button -->
    <div class="navigation">
        @if($parentId)
            <a href="/family-tree/{{ $parentId }}" class="nav-btn">
                ⬅️ Back to Parent
            </a>
            <a href="/family-tree" class="nav-btn home">
                🏠 Back to Root
            </a>
        @else
            <p><em>📍 You are at the root of the family tree</em></p>
        @endif
    </div>
    
    @if(isset($currentNode))
        <div class="member-card">
            <h2>{{ $currentNode['name'] ?? 'Unknown' }}</h2>
            
            <!-- Member Information -->
            <div class="member-info">
                <div class="info-row">
                    <span class="info-label">📅 Date of Birth:</span>
                    <span class="info-value {{ empty($currentNode['dob']) ? 'empty' : '' }}">
                        {{ $currentNode['dob'] ?? 'Not set' }}
                    </span>
                </div>
                
                @if(!empty($currentNode['dod']))
                    <div class="info-row">
                        <span class="info-label">⚰️ Date of Death:</span>
                        <span class="info-value">
                            {{ $currentNode['dod'] }}
                        </span>
                    </div>
                @endif
            </div>
            
            <!-- Biography Section -->
            @if(!empty($currentNode['bio']))
                <div class="bio-section">
                    <h3>📖 Biography</h3>
                    <div class="bio-text">{{ $currentNode['bio'] }}</div>
                </div>
            @endif
            
            <!-- Action Buttons -->
            <div class="actions">
                <button class="btn" onclick="addChild('{{ $currentNode['id'] }}')">
                    ➕ Add Child
                </button>
                
                <button class="btn edit" onclick="editMember('{{ $currentNode['id'] }}')">
                    ✏️ Edit Details
                </button>
                
                <button class="btn secondary" onclick="editBio('{{ $currentNode['id'] }}')">
                    📝 Edit Biography
                </button>
                
                @if($currentNode['id'] !== 'root')
                    <button class="btn delete" onclick="deleteMember('{{ $currentNode['id'] }}')">
                        🗑️ Delete
                    </button>
                @endif
            </div>
            
            <!-- Children Section -->
            @if(isset($currentNode['children']) && count($currentNode['children']) > 0)
                <div class="children-section">
                    <h3>👶 Children ({{ count($currentNode['children']) }})</h3>
                    <ul>
                        @foreach($currentNode['children'] as $child)
                            <li>
                                <a href="/family-tree/{{ $child['id'] }}">
                                    {{ $child['name'] ?? 'Unknown' }}
                                    @if(!empty($child['dob']))
                                        <small style="color: #666;">(Born: {{ $child['dob'] }})</small>
                                    @endif
                                    →
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="empty-state">
                    <p><strong>ℹ️ No children yet.</strong></p>
                    <p>Click "Add Child" above to add the first child.</p>
                </div>
            @endif
        </div>
    @else
        <div class="member-card">
            <p>❌ No tree data available.</p>
            <p><a href="/api/family-trees/init" class="btn">Initialize Tree</a></p>
        </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <a href="/family-tree">🏠 View Root</a> | 
        <a href="/api/family-trees/1">📄 View as JSON</a> | 
        <a href="/api/family-trees/1/stats">📊 View Statistics</a>
    </div>

    <!-- Edit Details Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal('editModal')">&times;</span>
                <h2>✏️ Edit Member Details</h2>
            </div>
            <div class="form-group">
                <label for="editName">Name:</label>
                <input type="text" id="editName" placeholder="Enter name">
            </div>
            <div class="form-group">
                <label for="editDob">Date of Birth:</label>
                <input type="date" id="editDob">
            </div>
            <div class="form-group">
                <label for="editDod">Date of Death (leave empty if alive):</label>
                <input type="date" id="editDod">
            </div>
            <div class="modal-actions">
                <button class="btn" style="background: #999;" onclick="closeModal('editModal')">Cancel</button>
                <button class="btn" onclick="saveDetails()">💾 Save</button>
            </div>
        </div>
    </div>

    <!-- Edit Biography Modal -->
    <div id="bioModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal('bioModal')">&times;</span>
                <h2>📝 Edit Biography</h2>
            </div>
            <div class="form-group">
                <label for="editBioText">Biography:</label>
                <textarea id="editBioText" rows="10" placeholder="Enter biography..."></textarea>
            </div>
            <div class="modal-actions">
                <button class="btn" style="background: #999;" onclick="closeModal('bioModal')">Cancel</button>
                <button class="btn" onclick="saveBio()">💾 Save</button>
            </div>
        </div>
    </div>

    <script>
        let currentEditingMemberId = null;

        function addChild(parentId) {
            const name = prompt('👶 Enter child name:');
            if (!name || name.trim() === '') {
                alert('⚠️ Name cannot be empty!');
                return;
            }
            
            fetch('/api/family-trees/members/add-child', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    parent_id: parentId,
                    name: name.trim() 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Child "' + name + '" added successfully!');
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error(error);
                alert('❌ Error: ' + error.message);
            });
        }

        // Edit Member - Open Modal
        function editMember(memberId) {
            currentEditingMemberId = memberId;
            
            // Get current values from the page
            const name = '{{ $currentNode['name'] ?? '' }}';
            const dob = '{{ $currentNode['dob'] ?? '' }}';
            const dod = '{{ $currentNode['dod'] ?? '' }}';
            
            // Set form values
            document.getElementById('editName').value = name;
            document.getElementById('editDob').value = dob;
            document.getElementById('editDod').value = dod;
            
            // Show modal
            document.getElementById('editModal').style.display = 'block';
        }

        // Save Details from Modal
        function saveDetails() {
            const name = document.getElementById('editName').value.trim();
            const dob = document.getElementById('editDob').value;
            const dod = document.getElementById('editDod').value;
            
            if (!name) {
                alert('⚠️ Name cannot be empty!');
                return;
            }
            
            // Update multiple fields
            const updates = [
                { field: 'name', value: name },
                { field: 'dob', value: dob },
                { field: 'dod', value: dod }
            ];
            
            Promise.all(updates.map(update => 
                fetch('/api/family-trees/members/update-field', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id: currentEditingMemberId,
                        field: update.field,
                        value: update.value
                    })
                }).then(r => r.json())
            ))
            .then(results => {
                const allSuccess = results.every(r => r.success);
                if (allSuccess) {
                    alert('✅ Details updated successfully!');
                    location.reload();
                } else {
                    alert('❌ Some updates failed');
                }
            })
            .catch(error => {
                console.error(error);
                alert('❌ Error: ' + error.message);
            });
        }

        // Edit Biography - Open Modal
        function editBio(memberId) {
            currentEditingMemberId = memberId;
            
            // Get current bio
            const bio = `{{ $currentNode['bio'] ?? '' }}`.replace(/&quot;/g, '"');
            
            // Set form value
            document.getElementById('editBioText').value = bio;
            
            // Show modal
            document.getElementById('bioModal').style.display = 'block';
        }

        function saveBio() {
            const bio = document.getElementById('editBioText').value;
            
            fetch('/api/family-trees/members/update-field', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    id: currentEditingMemberId,
                    field: 'bio',
                    value: bio
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Biography updated successfully!');
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error(error);
                alert('❌ Error: ' + error.message);
            });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        function deleteMember(memberId) {
            if (!confirm('⚠️ WARNING!\n\nAre you sure you want to delete this member?\nAll descendants will also be deleted!\n\nThis action cannot be undone.')) {
                return;
            }
            
            fetch('/api/family-trees/members/delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    id: memberId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Member deleted successfully!');
                    window.location.href = '/family-tree';
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error(error);
                alert('❌ Error: ' + error.message);
            });
        }
    </script>
</body>
</html>
