<?php
// footer.php - Modern Footer
require_once __DIR__ . "/../config/config.php";
?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <h3 class="footer-title"><?= SITE_NAME ?></h3>
                <p style="color:#AAA;"><?= SITE_TAGLINE ?></p>
                <p style="color:#AAA; margin-top:1rem;">
                    Votre boutique en ligne de confiance pour vêtements féminins, accessoires et produits pour bébé.
                </p>
            </div>
            
            <div>
                <h3 class="footer-title">Liens Utiles</h3>
                <a href="/shop.php" class="footer-link">Boutique</a>
                <a href="/contact.php" class="footer-link">Contact</a>
                <a href="/terms.php" class="footer-link">Conditions Générales</a>
                <a href="/privacy.php" class="footer-link">Politique de Confidentialité</a>
            </div>
            
            <div>
                <h3 class="footer-title">Contact</h3>
                <p style="color:#AAA; margin-bottom:0.5rem;">
                    <i class="fas fa-phone"></i> <?= CONTACT_PHONE ?>
                </p>
                <p style="color:#AAA; margin-bottom:0.5rem;">
                    <i class="fas fa-envelope"></i> <?= CONTACT_EMAIL ?>
                </p>
                <div style="margin-top:1rem; font-size:1.5rem;">
                    <a href="<?= FACEBOOK_URL ?>" target="_blank" style="color:#FFC1E3; margin-right:1rem;">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="<?= INSTAGRAM_URL ?>" target="_blank" style="color:#FFC1E3;">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés.
        </div>
    </div>
</footer>
