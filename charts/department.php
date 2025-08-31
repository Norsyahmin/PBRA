<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizational Chart - Management Structure</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f7f7f7;
            min-height: 100vh;
            color: #222;
        }

        .header {
            background: none;
            box-shadow: none;
            padding: 40px 0 0 0;
            margin-bottom: 0;
        }

        .header h1 {
            text-align: center;
            color: #222;
            font-weight: 700;
            font-size: 2.2rem;
            letter-spacing: 0.15em;
            margin-bottom: 30px;
        }

        .org-chart {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0;
            position: relative;
        }

        .chart-row {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            margin: 30px 0 0 0;
            position: relative;
        }

        .chart-card {
            background: #e7dfd7;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(44, 62, 80, 0.07);
            min-width: 170px;
            max-width: 170px;
            padding: 18px 12px 12px 12px;
            margin: 0 30px;
            text-align: center;
            position: relative;
            z-index: 2;
            border: none;
        }

        .chart-card .profile-image {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 10px auto;
            display: block;
            border: 3px solid #fff;
            background: #f4f4f4;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.07);
        }

        .chart-card .profile-placeholder {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #d2c3b3;
            margin: 0 auto 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #222;
            font-weight: 600;
            font-size: 22px;
            border: 3px solid #fff;
        }

        .chart-card .name {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 4px;
            color: #222;
        }

        .chart-card .role {
            font-size: 12px;
            font-weight: 500;
            color: #7a6e5a;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }

        /* Lines between cards */
        .chart-line {
            position: absolute;
            height: 2px;
            background: #b8b0a1;
            z-index: 1;
        }

        .chart-vertical {
            position: absolute;
            width: 2px;
            background: #b8b0a1;
            z-index: 1;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .chart-row {
                flex-wrap: wrap;
                margin: 20px 0 0 0;
            }

            .chart-card {
                margin: 10px;
                min-width: 140px;
                max-width: 140px;
            }
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            margin: 8% auto;
            padding: 40px;
            border-radius: 24px;
            width: 450px;
            max-width: 90%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .close {
            position: absolute;
            top: 20px;
            right: 25px;
            font-size: 24px;
            font-weight: 300;
            cursor: pointer;
            color: #a0aec0;
            transition: all 0.3s ease;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close:hover {
            color: #e53e3e;
            background: rgba(229, 62, 62, 0.1);
        }

        .modal-profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px auto;
            display: block;
            border: 4px solid #fff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-profile-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 32px;
            border: 4px solid #fff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .modal h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #2d3748;
            letter-spacing: -0.025em;
        }

        .modal-info {
            text-align: left;
            margin-top: 25px;
        }

        .modal-info p {
            margin-bottom: 12px;
            font-size: 16px;
            display: flex;
            align-items: center;
            padding: 8px 0;
        }

        .modal-info strong {
            min-width: 120px;
            color: #4a5568;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .modal-info i {
            margin-right: 8px;
            width: 16px;
            color: #667eea;
        }

        .highlight {
            animation: highlight-pulse 2s infinite;
        }

        @keyframes highlight-pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 20px 50px rgba(102, 126, 234, 0.4);
            }

            100% {
                transform: scale(1);
            }
        }

        .fade-in {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>COMPANY ORGANIZATION CHART</h1>
    </div>
    <div class="org-chart" id="orgChart">
        <!-- Top Owners -->
        <div class="chart-row" style="margin-top:0;">
            <div class="chart-card" onclick="showDetails('Daniel Gallego', 'Owner', '', '', '', 'images/daniel.jpg')">
                <img src="images/daniel.jpg" class="profile-image" alt="Daniel Gallego" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                <div class="profile-placeholder" style="display:none;">DG</div>
                <div class="name">Daniel Gallego</div>
                <div class="role">OWNER</div>
            </div>
            <div class="chart-card" onclick="showDetails('Adora Montminy', 'Owner', '', '', '', 'images/adora.jpg')">
                <img src="images/adora.jpg" class="profile-image" alt="Adora Montminy" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                <div class="profile-placeholder" style="display:none;">AM</div>
                <div class="name">Adora Montminy</div>
                <div class="role">OWNER</div>
            </div>
            <div class="chart-line" style="top:90px; left:50%; width:340px; margin-left:-170px;"></div>
        </div>
        <!-- CEO -->
        <div class="chart-row" style="margin-top:-10px;">
            <div class="chart-card" style="margin:0 auto;" onclick="showDetails('Chad Gibbons', 'CEO', '', '', '', 'images/chad.jpg')">
                <img src="images/chad.jpg" class="profile-image" alt="Chad Gibbons" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                <div class="profile-placeholder" style="display:none;">CG</div>
                <div class="name">Chad Gibbons</div>
                <div class="role">CEO</div>
            </div>
            <div class="chart-vertical" style="top:-30px; left:50%; height:30px; margin-left:-1px;"></div>
        </div>
        <!-- Managers -->
        <div class="chart-row">
            <div class="chart-card" onclick="showDetails('Estelle Darcy', 'Manager', '', '', '', 'images/estelle.jpg')">
                <img src="images/estelle.jpg" class="profile-image" alt="Estelle Darcy" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                <div class="profile-placeholder" style="display:none;">ED</div>
                <div class="name">Estelle Darcy</div>
                <div class="role">MANAGER</div>
            </div>
            <div class="chart-card" onclick="showDetails('Marceline Anderson', 'Manager', '', '', '', 'images/marceline.jpg')">
                <img src="images/marceline.jpg" class="profile-image" alt="Marceline Anderson" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                <div class="profile-placeholder" style="display:none;">MA</div>
                <div class="name">Marceline Anderson</div>
                <div class="role">MANAGER</div>
            </div>
            <div class="chart-card" onclick="showDetails('Francois Mercer', 'Manager', '', '', '', 'images/francois.jpg')">
                <img src="images/francois.jpg" class="profile-image" alt="Francois Mercer" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                <div class="profile-placeholder" style="display:none;">FM</div>
                <div class="name">Francois Mercer</div>
                <div class="role">MANAGER</div>
            </div>
            <div class="chart-card" onclick="showDetails('Richard Sanchez', 'Manager', '', '', '', 'images/richard.jpg')">
                <img src="images/richard.jpg" class="profile-image" alt="Richard Sanchez" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                <div class="profile-placeholder" style="display:none;">RS</div>
                <div class="name">Richard Sanchez</div>
                <div class="role">MANAGER</div>
            </div>
            <div class="chart-line" style="top:90px; left:50%; width:600px; margin-left:-300px;"></div>
        </div>
        <!-- Employees -->
        <div class="chart-row">
            <div style="display:flex; flex-direction:column;">
                <div class="chart-card" onclick="showDetails('Brigitte Schwartz', 'Employee', '', '', '', 'images/brigitte.jpg')">
                    <img src="images/brigitte.jpg" class="profile-image" alt="Brigitte Schwartz" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                    <div class="profile-placeholder" style="display:none;">BS</div>
                    <div class="name">Brigitte Schwartz</div>
                    <div class="role">EMPLOYEE</div>
                </div>
                <div class="chart-card" onclick="showDetails('Bailey Dupont', 'Employee', '', '', '', 'images/bailey.jpg')">
                    <img src="images/bailey.jpg" class="profile-image" alt="Bailey Dupont" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                    <div class="profile-placeholder" style="display:none;">BD</div>
                    <div class="name">Bailey Dupont</div>
                    <div class="role">EMPLOYEE</div>
                </div>
            </div>
            <div class="chart-card" onclick="showDetails('Lars Peeters', 'Employee', '', '', '', 'images/lars.jpg')">
                <img src="images/lars.jpg" class="profile-image" alt="Lars Peeters" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                <div class="profile-placeholder" style="display:none;">LP</div>
                <div class="name">Lars Peeters</div>
                <div class="role">EMPLOYEE</div>
            </div>
            <div class="chart-card" onclick="showDetails('Sacha Dubois', 'Employee', '', '', '', 'images/sacha.jpg')">
                <img src="images/sacha.jpg" class="profile-image" alt="Sacha Dubois" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                <div class="profile-placeholder" style="display:none;">SD</div>
                <div class="name">Sacha Dubois</div>
                <div class="role">EMPLOYEE</div>
            </div>
            <div style="display:flex; flex-direction:column;">
                <div class="chart-card" onclick="showDetails('Takehiro Kanegi', 'Employee', '', '', '', 'images/takehiro.jpg')">
                    <img src="images/takehiro.jpg" class="profile-image" alt="Takehiro Kanegi" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                    <div class="profile-placeholder" style="display:none;">TK</div>
                    <div class="name">Takehiro Kanegi</div>
                    <div class="role">EMPLOYEE</div>
                </div>
                <div class="chart-card" onclick="showDetails('Helene Paquet', 'Employee', '', '', '', 'images/helene.jpg')">
                    <img src="images/helene.jpg" class="profile-image" alt="Helene Paquet" onerror="this.style.display='none';this.parentNode.querySelector('.profile-placeholder').style.display='flex';">
                    <div class="profile-placeholder" style="display:none;">HP</div>
                    <div class="name">Helene Paquet</div>
                    <div class="role">EMPLOYEE</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Modal -->
    <div id="employeeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <div id="modalProfileImage" class="modal-profile-placeholder">?</div>
            <h2 id="modalName"></h2>
            <div class="modal-info">
                <p><strong><i class="fas fa-briefcase"></i>Position:</strong> <span id="modalPosition"></span></p>
                <p><strong><i class="fas fa-building"></i>Department:</strong> <span id="modalDepartment"></span></p>
                <p><strong><i class="fas fa-envelope"></i>Email:</strong> <span id="modalEmail"></span></p>
                <p><strong><i class="fas fa-phone"></i>Phone:</strong> <span id="modalPhone"></span></p>
            </div>
        </div>
    </div>

    <script>
        // Employee data
        const employees = [{
                name: 'Peter Murphy',
                position: 'Dean',
                department: 'Administration'
            },
            {
                name: 'Ronald Cox',
                position: 'Auxiliary Staff',
                department: 'Support'
            },
            {
                name: 'Mike Fox',
                position: 'Department Head',
                department: 'Dean\'s Office'
            },
            {
                name: 'Lou Silva',
                position: 'Department Head',
                department: 'Community'
            },
            {
                name: 'Marvin Lee',
                position: 'Department Head',
                department: 'Graduate Studies'
            },
            {
                name: 'Phillip Bert',
                position: 'Department Head',
                department: 'Programs'
            },
            {
                name: 'Kate Williams',
                position: 'Director',
                department: 'Academic Affairs'
            },
            {
                name: 'Holly Greene',
                position: 'Director',
                department: 'Development'
            },
            {
                name: 'Silvia Lewis',
                position: 'Director',
                department: 'Facilities'
            },
            {
                name: 'Lydia Chance',
                position: 'Director',
                department: 'Finance'
            },
            {
                name: 'Jason Patrick',
                position: 'Director',
                department: 'Exhibitions'
            },
            // ...additional employees...
        ];

        // Enhanced showDetails function
        function showDetails(name, position, department, email, phone, imagePath) {
            const modal = document.getElementById('employeeModal');
            const modalContent = modal.querySelector('.modal-content');

            document.getElementById('modalName').textContent = name;
            document.getElementById('modalPosition').textContent = position;
            document.getElementById('modalDepartment').textContent = department;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalPhone').textContent = phone;

            // Handle profile image in modal
            const modalProfileContainer = document.getElementById('modalProfileImage');
            modalProfileContainer.innerHTML = '';

            if (imagePath && imagePath !== 'images/default.jpg') {
                const img = document.createElement('img');
                img.src = imagePath;
                img.className = 'modal-profile-image';
                img.onerror = function() {
                    modalProfileContainer.className = 'modal-profile-placeholder';
                    modalProfileContainer.textContent = getInitials(name);
                };
                modalProfileContainer.appendChild(img);
                modalProfileContainer.className = '';
            } else {
                modalProfileContainer.className = 'modal-profile-placeholder';
                modalProfileContainer.textContent = getInitials(name);
            }

            modal.style.display = 'block';
            modalContent.style.animation = 'none';
            modalContent.offsetHeight; // Trigger reflow
            modalContent.style.animation = 'fadeInUp 0.4s ease-out';
        }

        // Get initials from name
        function getInitials(name) {
            return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase();
        }

        // Function to load actual images when available
        function loadProfileImages() {
            const nodes = document.querySelectorAll('.node');
            nodes.forEach(node => {
                const placeholder = node.querySelector('.profile-placeholder');
                if (placeholder) {
                    const name = node.querySelector('.name').textContent;
                    const imagePath = `images/${name.toLowerCase().replace(' ', '_')}.jpg`;

                    // Try to load the actual image
                    const img = new Image();
                    img.onload = function() {
                        placeholder.outerHTML = `<img src="${imagePath}" class="profile-image" alt="${name}">`;
                    };
                    img.onerror = function() {
                        // Keep the placeholder with initials if image doesn't exist
                        placeholder.textContent = getInitials(name);
                    };
                    img.src = imagePath;
                }
            });
        }

        // Close modal
        function closeModal() {
            document.getElementById('employeeModal').style.display = 'none';
        }

        // Enhanced search with debouncing
        let searchTimeout;
        document.getElementById('searchBox').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = e.target.value.toLowerCase();
                const nodes = document.querySelectorAll('.node');

                nodes.forEach(node => {
                    const name = node.querySelector('.name').textContent.toLowerCase();
                    const positionEl = node.querySelector('.position');
                    const departmentEl = node.querySelector('.department');

                    const position = positionEl ? positionEl.textContent.toLowerCase() : '';
                    const department = departmentEl ? departmentEl.textContent.toLowerCase() : '';

                    if (name.includes(searchTerm) || position.includes(searchTerm) || department.includes(searchTerm)) {
                        node.classList.add('highlight');
                        node.style.opacity = '1';
                        node.style.transform = 'scale(1)';
                    } else {
                        node.classList.remove('highlight');
                        if (searchTerm !== '') {
                            node.style.opacity = '0.3';
                            node.style.transform = 'scale(0.95)';
                        } else {
                            node.style.opacity = '1';
                            node.style.transform = 'scale(1)';
                        }
                    }
                });
            }, 300);
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('employeeModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Add some animation on page load
        window.addEventListener('load', function() {
            const nodes = document.querySelectorAll('.node');
            nodes.forEach((node, index) => {
                setTimeout(() => {
                    node.style.opacity = '0';
                    node.style.transform = 'translateY(20px)';
                    node.style.transition = 'all 0.5s ease';

                    setTimeout(() => {
                        node.style.opacity = '1';
                        node.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });

            // Load profile images
            setTimeout(loadProfileImages, 1000);
        });
    </script>

    <?php
    // PHP backend functionality for database integration

    // Example: Fetch employees from database
    function getEmployeesFromDB()
    {
        // Database connection would go here
        // $pdo = new PDO("mysql:host=localhost;dbname=company", $username, $password);

        // Sample data structure
        return [
            ['id' => 1, 'name' => 'Peter Murphy', 'position' => 'Dean', 'department' => 'Administration', 'manager_id' => null, 'image' => 'images/peter.jpg'],
            ['id' => 2, 'name' => 'Ronald Cox', 'position' => 'Auxiliary Staff', 'department' => 'Support', 'manager_id' => 1, 'image' => 'images/ronald.jpg'],
            ['id' => 3, 'name' => 'Mike Fox', 'position' => 'Department Head', 'department' => 'Dean\'s Office', 'manager_id' => 1, 'image' => 'images/mike.jpg'],
            // Add more employees...
        ];
    }

    // Example: Add new employee
    function addEmployee($name, $position, $department, $manager_id)
    {
        // Database insertion logic
        // $sql = "INSERT INTO employees (name, position, department, manager_id) VALUES (?, ?, ?, ?)";
        // Execute query...
        return true;
    }

    // Example: Update employee
    function updateEmployee($id, $name, $position, $department)
    {
        // Database update logic
        return true;
    }

    // Example: Delete employee
    function deleteEmployee($id)
    {
        // Database deletion logic
        return true;
    }

    // Function to handle profile image upload
    function uploadProfileImage($employeeId, $imageFile)
    {
        $targetDir = "images/";
        $imageFileType = strtolower(pathinfo($imageFile["name"], PATHINFO_EXTENSION));
        $targetFile = $targetDir . $employeeId . "." . $imageFileType;

        // Check if image file is actual image
        $check = getimagesize($imageFile["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($imageFile["tmp_name"], $targetFile)) {
                return $targetFile;
            }
        }
        return false;
    }
    ?>
</body>

</html>