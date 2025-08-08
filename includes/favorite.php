<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../mypbra_connect.php'; // ✅ correct because mypbra_connect is outside

$user_id = $_SESSION['id'] ?? 0;
$page_name = $page_name ?? basename($_SERVER['PHP_SELF'], ".php"); // fallback
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$isFavorited = false;
if ($user_id && $page_name) {
    $stmt = $conn->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND page_name = ?");
    $stmt->bind_param("is", $user_id, $page_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $isFavorited = ($result->num_rows > 0);
    $stmt->close();
}
?>




<style>
.favorite-button {
    background: #ffffff;
    border: 2px solid #174080;
    color: #174080;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.favorite-button:hover {
    background: #4a89dc;
    color: #fff;
}

.favorite-button.favorited {
    background: #4a89dc;
    color: white;
    border-color: #4a89dc;
}
</style>

<button 
    type="button" 
    id="favoriteButton" 
    class="favorite-button <?php echo $isFavorited ? 'favorited' : ''; ?>" 
    onclick="toggleFavorite()">
    <?php echo $isFavorited ? 'Favorited' : 'Add to Favorite'; ?>
</button>

<script>
function toggleFavorite() {
    const button = document.getElementById('favoriteButton');
    const pageName = "<?php echo $page_name; ?>";
    const pageUrl = "<?php echo $page_url; ?>";

    let action = '';

    if (!button.classList.contains('favorited')) {
        button.classList.add('favorited');
        button.textContent = 'Favorited';
        action = 'add';
    } else {
        button.classList.remove('favorited');
        button.textContent = 'Add to Favorite';
        action = 'remove';
    }

    fetch('favorite_action.php', {  // ✅ SAME FOLDER, no '../'
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'page_name=' + encodeURIComponent(pageName) + 
              '&page_url=' + encodeURIComponent(pageUrl) +
              '&action=' + encodeURIComponent(action)
    })
    .catch(error => console.error('Error:', error));
}
</script>
