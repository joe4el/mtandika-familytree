<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Six Generation Family Tree</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            margin: 0;
            background: #f4f4f9;
            font-family: Arial, sans-serif;
        }
        .wrapper { text-align: center; }
        h1 { font-size: 28px; margin: 20px 0; }
        .tree ul {
            padding-top: 20px;
            position: relative;
            display: table;
            margin: 0 auto;
        }
        .tree li {
            display: table-cell;
            padding: 20px 5px;
            vertical-align: top;
            position: relative;
        }
        .tree li::before, .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 2px solid #ccc;
            width: 50%;
            height: 20px;
        }
        .tree li::after {
            right: auto;
            left: 50%;
            border-left: 2px solid #ccc;
        }
        .tree li:only-child::before,
        .tree li:only-child::after {
            display: none;
        }
        .tree li:only-child { padding-top: 0; }
        .tree li:first-child::before,
        .tree li:last-child::after { border: none; }
        .tree li:last-child::before { border-right: 2px solid #ccc; }
        .member {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 8px;
            background: #fff;
            border: 2px solid #4a90e2;
            min-width: 140px;
            position: relative;
        }
        .member input {
            border: none;
            outline: none;
            background: transparent;
            text-align: center;
            width: 100%;
        }
        .controls { text-align: center; margin: 20px; }
        button {
            padding: 8px 15px;
            border: none;
            background: #4a90e2;
            color: #fff;
            border-radius: 5px;
            margin: 0 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>🌳 Six Generation Family Tree 🌳</h1>

        <div class="controls">
            <button id="editBtn">Edit</button>
            <button id="saveBtn">Save Changes</button>
        </div>

        <div class="tree" id="treeContainer"></div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let editing = false;

        let treeData = @json($treeData); // ✅ Loaded from DB via controller

        function buildTree(node) {
            let html = `<li>
                <div class="member">
                    <input type="text" value="${node.name}" data-id="${node.id}" ${editing ? "" : "disabled"}>
                </div>`;
            if (node.children && node.children.length > 0) {
                html += `<ul>`;
                node.children.forEach(child => {
                    html += buildTree(child);
                });
                html += `</ul>`;
            }
            html += `</li>`;
            return html;
        }

        function renderTree() {
            document.getElementById('treeContainer').innerHTML = `<ul>${buildTree(treeData)}</ul>`;
        }

        function updateNames(node) {
            const input = document.querySelector(`input[data-id="${node.id}"]`);
            if (input) node.name = input.value;
            if (node.children) node.children.forEach(updateNames);
        }

        document.getElementById('editBtn').addEventListener('click', () => {
            editing = !editing;
            renderTree();
            document.getElementById('editBtn').textContent = editing ? "Stop Editing" : "Edit";
        });

        document.getElementById('saveBtn').addEventListener('click', () => {
            updateNames(treeData);

            fetch('/save-tree', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(treeData)
            })
            .then(res => res.json())
            .then(data => {
                alert("✅ Tree saved successfully!");
            })
            .catch(err => console.error("Save failed:", err));
        });

        renderTree();
    </script>
</body>
</html>
