<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>IT Department Organization Chart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f9f9f9;
            color: #333;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #555;
            font-weight: 400;
        }

        .search-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .search-container input {
            width: 300px;
            max-width: 90%;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        /* Org Chart Styles */
        .org-tree {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .org-tree ul {
            padding-top: 20px;
            position: relative;
            display: flex;
            justify-content: center;
        }

        .org-tree ul ul {
            padding-top: 30px;
        }

        .org-tree li {
            list-style-type: none;
            text-align: center;
            position: relative;
            padding: 20px 5px 0 5px;
            transition: all 0.5s;
        }

        /* Connector lines */
        .org-tree li::before,
        .org-tree li::after {
            content: '';
            position: absolute;
            top: 0;
            border-top: 2px solid #aaa;
            width: 50%;
            height: 20px;
        }

        .org-tree li::before {
            left: 0;
            border-left: 2px solid #aaa;
        }

        .org-tree li::after {
            right: 0;
            border-right: 2px solid #aaa;
        }

        /* Remove lines for root */
        .org-tree>ul>li::before,
        .org-tree>ul>li::after {
            border: none;
        }

        /* Remove left line for first child, right for last child */
        .org-tree li:only-child::before,
        .org-tree li:only-child::after {
            border: none;
        }

        .org-tree li:first-child::before {
            border: none;
        }

        .org-tree li:last-child::after {
            border: none;
        }

        /* Card Styles */
        .chart-card {
            background: #f0edeb;
            border-radius: 8px;
            padding: 10px 15px;
            width: 170px;
            min-height: 120px;
            text-align: center;
            border: 1px solid #d3d3d3;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
            margin: 0 auto;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: box-shadow 0.3s, border-color 0.3s;
        }

        .chart-card .image-container {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #fff;
            border: 1px solid #d3d3d3;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 5px;
            font-size: 20px;
            color: #888;
        }

        .chart-card .short-name {
            font-size: 11px;
            color: #888;
            margin-bottom: 2px;
            line-height: 1;
        }

        .chart-card .name {
            font-weight: 700;
            font-size: 13px;
            color: #333;
            margin-bottom: 3px;
        }

        .chart-card .role-title {
            font-size: 9px;
            font-weight: 400;
            color: #777;
            text-transform: uppercase;
            line-height: 1;
        }

        .highlight {
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.4);
            border-color: #2563eb;
        }

        .dimmed {
            opacity: 0.3;
            filter: grayscale(100%);
        }

        /* Responsive */
        @media (max-width: 900px) {
            .org-tree ul {
                flex-direction: column;
                align-items: center;
            }

            .org-tree li {
                padding: 20px 0 0 0;
            }

            .chart-card {
                width: 90vw;
                min-width: 0;
            }

            .org-tree li::before,
            .org-tree li::after {
                width: 2px;
                height: 20px;
                border-top: none;
            }
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            max-width: 90%;
            text-align: center;
            position: relative;
            animation: fadeIn 0.3s ease;
        }

        .modal-content img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
            background-color: #f0f0f0;
            border: 1px solid #eee;
            object-fit: cover;
        }

        .modal-content .modal-icon {
            font-size: 80px;
            color: #888;
            margin-bottom: 15px;
        }

        .modal-content h2 {
            margin-bottom: 10px;
        }

        .modal-content p {
            margin: 6px 0;
            font-size: 15px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 22px;
            cursor: pointer;
            color: #666;
        }

        .close:hover {
            color: #e53e3e;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <h1>IT DEPARTMENT ORGANIZATION CHART</h1>
    <div class="search-container">
        <input type="text" id="searchBox" placeholder="Search by name or role...">
    </div>
    <div class="org-tree">
        <ul>
            <li>
                <div class="chart-card" data-name="Daniel Gallegos" data-role="IT Owner" data-department="Executive" data-email="daniel.g@example.com" data-phone="123-456-7890" data-image="">
                    <div class="image-container"><i class="fas fa-user"></i></div>
                    <div class="short-name">Daniel Gallegos</div>
                    <div class="name">Daniel Gallegos</div>
                    <div class="role-title">IT OWNER</div>
                </div>
                <ul>
                    <li>
                        <div class="chart-card" data-name="Adora Montminy" data-role="IT Owner" data-department="Executive" data-email="adora.m@example.com" data-phone="987-654-3210" data-image="">
                            <div class="image-container"><i class="fas fa-user"></i></div>
                            <div class="short-name">Adora Montminy</div>
                            <div class="name">Adora Montminy</div>
                            <div class="role-title">IT OWNER</div>
                        </div>
                        <ul>
                            <li>
                                <div class="chart-card" data-name="Chad Gibbons" data-role="CTO" data-department="Executive" data-email="chad.g@example.com" data-phone="111-222-3333" data-image="">
                                    <div class="image-container"><i class="fas fa-user"></i></div>
                                    <div class="short-name">Chad Gibbons</div>
                                    <div class="name">Chad Gibbons</div>
                                    <div class="role-title">CTO</div>
                                </div>
                                <ul>
                                    <li>
                                        <div class="chart-card" data-name="Estelle Darcy" data-role="Infrastructure Manager" data-department="Infrastructure" data-email="estelle.d@example.com" data-phone="444-555-6666" data-image="">
                                            <div class="image-container"><i class="fas fa-user"></i></div>
                                            <div class="short-name">Estelle Darcy</div>
                                            <div class="name">Estelle Darcy</div>
                                            <div class="role-title">INFRASTRUCTURE MANAGER</div>
                                        </div>
                                        <ul>
                                            <li>
                                                <div class="chart-card" data-name="Brigitte Schwartz" data-role="Network Engineer" data-department="Infrastructure" data-email="brigitte.s@example.com" data-phone="888-999-0000" data-image="">
                                                    <div class="image-container"><i class="fas fa-user"></i></div>
                                                    <div class="short-name">Brigitte Schwartz</div>
                                                    <div class="name">Brigitte Schwartz</div>
                                                    <div class="role-title">NETWORK ENGINEER</div>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <div class="chart-card" data-name="Bailey Dupont" data-role="IT Support Specialist" data-department="Infrastructure" data-email="bailey.d@example.com" data-phone="333-444-5555" data-image="">
                                                            <div class="image-container"><i class="fas fa-user"></i></div>
                                                            <div class="short-name">Bailey Dupont</div>
                                                            <div class="name">Bailey Dupont</div>
                                                            <div class="role-title">IT SUPPORT SPECIALIST</div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <div class="chart-card" data-name="Marceline Anderson" data-role="DevOps Manager" data-department="DevOps" data-email="marceline.a@example.com" data-phone="555-666-7777" data-image="">
                                            <div class="image-container"><i class="fas fa-user"></i></div>
                                            <div class="short-name">Marceline Anderson</div>
                                            <div class="name">Marceline Anderson</div>
                                            <div class="role-title">DEVOPS MANAGER</div>
                                        </div>
                                        <ul>
                                            <li>
                                                <div class="chart-card" data-name="Lars Peeters" data-role="DevOps Engineer" data-department="DevOps" data-email="lars.p@example.com" data-phone="000-111-2222" data-image="">
                                                    <div class="image-container"><i class="fas fa-user"></i></div>
                                                    <div class="short-name">Lars Peeters</div>
                                                    <div class="name">Lars Peeters</div>
                                                    <div class="role-title">DEVOPS ENGINEER</div>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <div class="chart-card" data-name="Francois Mercer" data-role="Development Manager" data-department="Software Development" data-email="francois.m@example.com" data-phone="666-777-8888" data-image="">
                                            <div class="image-container"><i class="fas fa-user"></i></div>
                                            <div class="short-name">Francois Mercer</div>
                                            <div class="name">Francois Mercer</div>
                                            <div class="role-title">DEVELOPMENT MANAGER</div>
                                        </div>
                                        <ul>
                                            <li>
                                                <div class="chart-card" data-name="Sacha Dubois" data-role="Frontend Developer" data-department="Software Development" data-email="sacha.d@example.com" data-phone="111-222-3333" data-image="">
                                                    <div class="image-container"><i class="fas fa-user"></i></div>
                                                    <div class="short-name">Sacha Dubois</div>
                                                    <div class="name">Sacha Dubois</div>
                                                    <div class="role-title">FRONTEND DEVELOPER</div>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <div class="chart-card" data-name="Richard Sanchez" data-role="QA Manager" data-department="Quality Assurance" data-email="richard.s@example.com" data-phone="777-888-9999" data-image="">
                                            <div class="image-container"><i class="fas fa-user"></i></div>
                                            <div class="short-name">Richard Sanchez</div>
                                            <div class="name">Richard Sanchez</div>
                                            <div class="role-title">QA MANAGER</div>
                                        </div>
                                        <ul>
                                            <li>
                                                <div class="chart-card" data-name="Takehiro Kanegi" data-role="Senior QA Engineer" data-department="Quality Assurance" data-email="takehiro.k@example.com" data-phone="222-333-4444" data-image="">
                                                    <div class="image-container"><i class="fas fa-user"></i></div>
                                                    <div class="short-name">Takehiro Kanegi</div>
                                                    <div class="name">Takehiro Kanegi</div>
                                                    <div class="role-title">SENIOR QA ENGINEER</div>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <div class="chart-card" data-name="Helene Paquet" data-role="QA Tester" data-department="Quality Assurance" data-email="helene.p@example.com" data-phone="444-555-6666" data-image="">
                                                            <div class="image-container"><i class="fas fa-user"></i></div>
                                                            <div class="short-name">Helene Paquet</div>
                                                            <div class="name">Helene Paquet</div>
                                                            <div class="role-title">QA TESTER</div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <!-- Modal -->
    <div id="employeeModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <img id="modalImage" src="" alt="Profile">
            <h2 id="modalName"></h2>
            <p><strong>Role:</strong> <span id="modalRole"></span></p>
            <p><strong>Department:</strong> <span id="modalDepartment"></span></p>
            <p><strong>Email:</strong> <span id="modalEmail"></span></p>
            <p><strong>Phone:</strong> <span id="modalPhone"></span></p>
        </div>
    </div>
    <script>
        const searchBox = document.getElementById("searchBox");
        const cards = document.querySelectorAll(".chart-card");
        const modal = document.getElementById("employeeModal");
        const closeBtn = modal.querySelector(".close");
        const modalName = document.getElementById("modalName");
        const modalRole = document.getElementById("modalRole");
        const modalDepartment = document.getElementById("modalDepartment");
        const modalEmail = document.getElementById("modalEmail");
        const modalPhone = document.getElementById("modalPhone");
        const modalImage = document.getElementById("modalImage");

        // Search functionality
        searchBox.addEventListener("input", function() {
            const term = this.value.toLowerCase();
            cards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const role = card.dataset.role.toLowerCase();
                const dept = card.dataset.department.toLowerCase();
                if (name.includes(term) || role.includes(term) || dept.includes(term)) {
                    card.classList.add("highlight");
                    card.classList.remove("dimmed");
                } else {
                    card.classList.remove("highlight");
                    if (term !== "") {
                        card.classList.add("dimmed");
                    } else {
                        card.classList.remove("dimmed");
                    }
                }
            });
        });

        // Open modal on card click
        cards.forEach(card => {
            card.addEventListener("click", () => {
                modalName.textContent = card.dataset.name;
                modalRole.textContent = card.dataset.role;
                modalDepartment.textContent = card.dataset.department;
                modalEmail.textContent = card.dataset.email;
                modalPhone.textContent = card.dataset.phone;
                modalImage.src = card.dataset.image || '';
                modalImage.alt = card.dataset.name + " profile";
                modalImage.style.display = card.dataset.image ? 'block' : 'none';
                // If no image, show icon
                if (!card.dataset.image) {
                    if (!modal.querySelector('.modal-icon')) {
                        const icon = document.createElement('i');
                        icon.className = 'fas fa-user modal-icon';
                        modal.querySelector('.modal-content').insertBefore(icon, modal.querySelector('h2'));
                    }
                } else {
                    const icon = modal.querySelector('.modal-icon');
                    if (icon) icon.remove();
                }
                modal.style.display = "flex";
            });
        });

        // Close modal
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
        window.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });
    </script>
    <?php include '../dashboard_template/footer/footer.php'; ?>
    <?php include '../dashboard_template/scrolltop/scrolltop.php'; ?>
</body>

</html>