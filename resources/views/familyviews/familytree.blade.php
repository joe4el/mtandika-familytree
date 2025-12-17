
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Mtandika Family Tree</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 50px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            font-size: 2rem;
            color: #14532d;
            text-align: center;
            margin-bottom: 40px;
            width: 100%;
            text-align: center;
            display: block;
        }

        .tree {
            position: relative;
            text-align: center;
            padding: 20px;
            scroll-behavior: smooth;
            width: max-content;      
            margin: 0 auto;       
        }

        .tree ul {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 20px;
            z-index: 1;
            transition: all 0.5s;
            list-style-type: none;
        }

        .tree li {
            position: relative;
            margin: 0 25px;
            padding: 20px 5px 0 5px;
            z-index: 1;
            text-align: center;
            list-style-type: none;
        }

        .tree li::before, .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            border-top: 2px solid #555;
            width: 50%;
            height: 20px;
        }


        .tree li::before { right: 50%; border-right: 2px solid #555; }
        .tree li::after { left: 50%; border-left: 2px solid #555; }

        .tree li:only-child::before,
        .tree li:only-child::after { display: none; }

        .tree > ul > li::before,
        .tree > ul > li::after { border: none; }

        .tree li ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 2px solid #555;
            height: 20px;
        }

        .member {
            background: white;
            border: 2px solid #15803d;
            color: #064e3b;
            display: inline-block;
            padding: 10px 20px;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: bold;
            cursor: pointer !important;
            position: relative;
            z-index: 10;
            min-width: 150px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }

        .member:hover {
            background: #15803d;
            color: white;
            transform: scale(1.05);
        }

        .member input {
            border: none;
            background: transparent;
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            color: inherit;
            width: 100%;
        }

        .member input:focus {
            outline: none;
            background: #f0fdf4;
        }

        .member a {
            text-decoration: none;
            color: inherit;
        }

        .member, .member-name {
            cursor: pointer !important;
            user-select: none;
        }
      
       .member-box {
            position: relative;
            display: inline-block; /* Change from inline-flex to inline-block */
            padding: 10px;
            cursor: pointer;
            }

        .member, .member-box {
            cursor: pointer;
            }

        .member-options {
            position: absolute;
            right: -90px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
            flex-direction: column;
            gap: 5px;
            z-index: 5000; /* Lower than hover-card */
            background: white;
            padding: 6px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }

        .member-options::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            z-index: -1;
            pointer-events: none;
        }

        .member-options button {
            pointer-events: auto;
            background: #f1f1f1;
            border: none;
            padding: 6px 8px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
        }

        .member-options button:hover {
        background: #ddd;
        }

        .member-info {
        font-size: 12px;
        color: #374151;
        margin-top: 4px;
        }

        .member-wrapper {
            position: relative;
            display: inline-block;
            z-index: 100;
            padding: 10px;
        }

        .member-wrapper::before {
            content: '';
            position: absolute;
            top: -15px;
            left: -15px;
            right: -15px;
            bottom: -15px;
            z-index: 1;
            pointer-events: none; /* Don't interfere with clicks */
        }

        .member-info small {
            display: block;
        }

        .member-options:hover {
            display: flex !important;
        }

        .hover-card {
            position: absolute;
            top: calc(100% + 5px); /* Position just below the member */
            left: 0;
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 9999;
            min-width: 180px;
            border: 1px solid #e5e7eb;
            pointer-events: auto; /* Allow interaction */
        }

        .hover-card::before {
            content: '';
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 6px solid white;
            z-index: 10002;
        }

        .hover-card button {
            position: relative;
            pointer-events: auto !important;
            cursor: pointer;
            z-index: 9980;
        }

        .hover-card + .member-options {
            margin-top: 10px;
        }

        .member-wrapper:hover .hover-card {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
        }

        .member-wrapper:hover .member-options {
            display: flex !important;
        }

        .controls {
            margin-top: 20px;
        }

        .controls button {
            background: #15803d;
            color: white;
            border: none;
            padding: 10px 15px;
            margin: 0 5px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .controls button:hover {
            background: #166534;
        }

        .tooltip {
        position: absolute;
        top: -10px;
        left: 100%;
        margin-left: 10px;

        background: #222;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 13px;

        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease-in-out;
        white-space: nowrap;
        z-index: 999;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 30px;
            background: #dc2626;
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .selected {
            border: 3px solid #f59e0b !important;
            background: #fef9c3 !important;
        }


        .back-button:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
   @if($parentId)
    <a href="{{ route('familytree.show', ['id' => $parentId]) }}" class="back-button">← Go Back</a>
    @endif

    <h1>🌳 MTANDIKA FAMILY TREE 🌳</h1>

    @if(auth()->check() && auth()->user()->is_admin)
    <div class="controls">
        <button onclick="toggleEdit()">✏️ Edit</button>
        <button onclick="saveTree()">💾 Save</button>
        <button onclick="addChild()">➕ Add Child</button>
        <button onclick="deleteMember()">🗑️ Delete Member</button>
    </div>
    @endif

    <div class="tree" id="tree"></div>

    <script>
    const isAdmin = true;
    const treeData = @json($currentNode);
    let editing = false;
    let deleteMode = false;

    function buildTree(node) {
    let html = `
        <ul><li>
           <div class="member-wrapper">
                <div class="member-box">

                    <div class="member" data-id="${node.id}">
                        <span class="member-name">${node.name}</span>
                    </div>
                 
                    <div class="hover-card">
                        <strong>${node.name}</strong><br>
                        ${node.dob ? `📅 DOB: ${node.dob}<br>` : ""}
                        ${node.dod ? `⚰️ DOD: ${node.dod}<br>` : ""}
                        ${node.bio ? `<hr><div>${node.bio}</div>` : ""}
                    </div>
                    
                    ${isAdmin ? `
                        <div class="member-options">
                            <button onclick="editField('dob', '${node.id}')">📅 DOB</button>
                            <button onclick="editField('dod', '${node.id}')">⚰️ DOD</button>
                            <button onclick="editField('bio', '${node.id}')">🧬 Bio</button>
                        </div>
                    ` : ""}
                </div>
            </div>
        `;

        if (node.children && node.children.length > 0) {
            html += `<ul>`;
            node.children.forEach(child => {
                html += `
                <li>
                    <div class="member-wrapper">
                        <div class="member-box">

                            <div class="member" data-id="${child.id}">
                                <span class="member-name">${child.name}</span>

                                <div class="hover-card">
                                    <strong>${child.name}</strong><br>
                                    ${child.dob ? `📅 DOB: ${child.dob}<br>` : ""}
                                    ${child.dod ? `⚰️ DOD: ${child.dod}<br>` : ""}
                                    ${child.bio ? `<hr><div>${child.bio}</div>` : ""}
                                </div>
                            </div>

                            ${isAdmin ? `
                            <div class="member-options">
                                <button onclick="editField('dob', '${child.id}')">📅 DOB</button>
                                <button onclick="editField('dod', '${child.id}')">⚰️ DOD</button>
                                <button onclick="editField('bio', '${child.id}')">🧬 Bio</button>
                            </div>
                            ` : ""}
                        </div>
                    </div>
                </li>`;
            });
            html += `</ul>`;
        }

        html += `</li></ul>`;
        return html;
    }

    document.addEventListener("click", function (e) {
        const member = e.target.closest(".member");

        if (!member) return;

        // Ignore clicks inside controls
        if (e.target.closest('.member-options') || e.target.closest('.hover-card')) {
            return;
        }

        const id = member.dataset.id;
        window.location.href = `/family-tree/${id}`;
    });

    function renderTree() {
        const treeContainer = document.getElementById('tree');

        if (!treeData || !treeData.id) {
            treeContainer.innerHTML = "<p>❌ Tree data not available</p>";
            return;
        }

        treeContainer.innerHTML = buildTree(treeData);

        if (deleteMode) {
            enableDeleteSelection();
        }
    }


    function collectTreeData(node) {
        const nameInput = document.querySelector(`input[data-id="${node.id}"]`);
        node.name = nameInput ? nameInput.value : node.name;

        if (node.children && node.children.length > 0) {
            node.children.forEach(child => collectTreeData(child));
        }
    }

   function saveTree() {
    collectTreeData(treeData);

    fetch("{{ route('familytree.save') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            id: treeData.id,
            tree: treeData
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Family tree saved successfully!');
            editing = false;
            renderTree();
        } else {
            alert('❌ Save failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('❌ Error saving tree: ' + err.message);
        console.error(err);
    });
    }

    function addChild() {
        const newId = `${treeData.id}-${(treeData.children ? treeData.children.length + 1 : 1)}`;
        const newChild = {
            id: newId,
            name: 'New Family Member',
            children: []
        };

        treeData.children = treeData.children || [];
        treeData.children.push(newChild);
        renderTree();
    }

    function toggleDeleteMode() {
        deleteMode = !deleteMode;
        editing = false;

        if (deleteMode) {
            alert("🗑️ Delete Mode Activated: Click a family member to delete them.");
        }

        renderTree();
    }

    function enableDeleteSelection() {
        document.querySelectorAll('.member').forEach(div => {
            div.style.border = '2px solid #dc2626';
            div.style.cursor = 'pointer';

            div.addEventListener('click', handleDeleteClick);
        });
    }

    function editField(field, id)
    {
        let value = prompt("Enter " + field.toUpperCase() + ":");
        if (!value) return;

        fetch('/update-member-field', {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id: id, field: field, value: value })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {

                updateLocalMember(treeData, id, field, value); // update UI memory
                renderTree();                                 // redraw UI

                alert(field.toUpperCase() + " updated!");
            } else {
                alert("Update failed: " + data.message);
            }
        });
    }

    function updateLocalMember(node, id, field, value) {
    if (node.id == id) {
        node[field] = value;
        return true;
    }

    if (node.children) {
        for (let child of node.children) {
            if (updateLocalMember(child, id, field, value)) {
                return true;
            }
        }
    }

    return false;
    }

    function handleDeleteClick(e) {
        e.preventDefault();
        const memberId = e.currentTarget.getAttribute('data-id');

        if (confirm(`Are you sure you want to delete this member and all their descendants?`)) {
            fetch('/delete-member', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ id: memberId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Member deleted successfully!');
                    window.location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(err => alert('❌ Error deleting: ' + err));
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderTree();

        const controls = document.querySelector('.controls');
        if (controls && isAdmin) {
            controls.innerHTML = `
                <button onclick="toggleEdit()">✏️ Edit</button>
                <button onclick="saveTree()">💾 Save</button>
                <button onclick="addChild()">➕ Add Child</button>
                <button onclick="toggleDeleteMode()">🗑️ Delete</button>
            `;
        }
    });

</script>

</body>
</html>
