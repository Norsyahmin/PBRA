<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PHP Website with Components</title>
    <!-- Link to global CSS file -->
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <?php include '../dashboard_template/navbar/navbar.php'; ?>

    <!-- Main content -->
    <div id="content" class="content">
        <h1>Welcome to My PHP Website</h1>
        <p>This is the main content area. All CSS and JavaScript are now in separate files!</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, nunc at
            facilisis tincidunt, justo erat tincidunt nulla, nec ultricies libero nulla
            nec lorem. Curabitur nec lorem vel sapien fermentum dictum. Donec vel
            tincidunt lorem. Suspendisse potenti.</p>
        <p style="height: 2000px;">Keep scrolling...</p>
    </div>

    <?php include '../dashboard_template/scrolltop/scrolltop.php'; ?>
    <?php include '../footer/footer.php'; ?>

    <!-- Link to external JavaScript files -->
    <script src="../dashboard_template/navbar/navbar.js"></script>
    <script src="../dashboard_template/scrolltop/scrolltop.js"></script>
</body>

</html>