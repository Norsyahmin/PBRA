 <div class="language-switcher-container">
     <div class="language-trigger" id="languageTrigger">
         <span><?= htmlspecialchars($supported_languages[$current_language]['name']); ?></span>
         <i class="fas fa-chevron-down arrow-icon"></i>
     </div>

     <div class="language-dropdown" id="languageDropdown">
         <?php foreach ($supported_languages as $code => $data) : ?>
             <a href="?lang=<?= htmlspecialchars($code); ?>"
                 class="<?= ($code === $current_language) ? 'active' : ''; ?>">
                 <?= htmlspecialchars($data['name']); ?>
             </a>
         <?php endforeach; ?>
     </div>
 </div>