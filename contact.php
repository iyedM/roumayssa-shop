<?php
session_start();
require_once "includes/db.php";
require_once "config/config.php";

// SEO Meta
$pageTitle = "Contact";
$pageDescription = "Contactez-nous pour toute question ou demande. Nous sommes à votre écoute.";

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } else {
        try {
            // Save to database
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (full_name, email, phone, message, lu, created_at) 
                VALUES (:name, :email, :phone, :message, 0, NOW())
            ");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message
            ]);
            
            $success = "Merci pour votre message ! Nous vous répondrons dans les plus brefs délais.";
            
            // Clear form
            $name = $email = $phone = $message = '';
        } catch (Exception $e) {
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<?php include 'templates/navbar.php'; ?>

<div class="container section">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="/index.php">Accueil</a>
        <span class="breadcrumb-separator">/</span>
        <span>Contact</span>
    </div>
    
    <h1 class="text-center mb-4">Contactez-nous</h1>
    <p class="text-center text-muted mb-4">
        Nous sommes là pour vous aider ! N'hésitez pas à nous contacter.
    </p>
    
    <div class="grid grid-2" style="gap:2rem;">
        <!-- Contact Form -->
        <div class="card">
            <h2 class="mb-3"><i class="fas fa-envelope"></i> Envoyez-nous un message</h2>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/contact.php">
                <div class="form-group">
                    <label class="form-label" for="name">Nom complet <span style="color:var(--error-red);">*</span></label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-control" 
                           value="<?= htmlspecialchars($name ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email <span style="color:var(--error-red);">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           value="<?= htmlspecialchars($email ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Téléphone</label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           class="form-control" 
                           value="<?= htmlspecialchars($phone ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="message">Message <span style="color:var(--error-red);">*</span></label>
                    <textarea id="message" 
                              name="message" 
                              class="form-control" 
                              rows="6" 
                              required><?= htmlspecialchars($message ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-paper-plane"></i> Envoyer le message
                </button>
            </form>
        </div>
        
        <!-- Contact Information -->
        <div>
            <!-- WhatsApp Card -->
            <div class="card mb-3" style="background: linear-gradient(135deg, #25D366, #128C7E); color:white;">
                <h3 style="color:white; margin-bottom:1rem;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </h3>
                <p style="opacity:0.95; margin-bottom:1.5rem;">
                    Pour une réponse rapide, contactez-nous directement sur WhatsApp !
                </p>
                <a href="https://wa.me/<?= str_replace(['+', ' '], '', WHATSAPP_NUMBER) ?>?text=Bonjour,%20j'ai%20une%20question" 
                   class="btn btn-secondary btn-block" 
                   target="_blank">
                    <i class="fab fa-whatsapp"></i> Discuter sur WhatsApp
                </a>
            </div>
            
            <!-- Contact Details -->
            <div class="card mb-3">
                <h3 class="mb-3"><i class="fas fa-info-circle"></i> Informations</h3>
                
                <div style="margin-bottom:1rem;">
                    <div style="display:flex; gap:1rem; margin-bottom:0.75rem;">
                        <i class="fas fa-phone" style="color:var(--primary-pink); flex-shrink:0; width:20px;"></i>
                        <div>
                            <strong>Téléphone</strong><br>
                            <a href="tel:<?= CONTACT_PHONE ?>" style="color:var(--primary-pink);">
                                <?= CONTACT_PHONE ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom:1rem;">
                    <div style="display:flex; gap:1rem; margin-bottom:0.75rem;">
                        <i class="fas fa-envelope" style="color:var(--primary-pink); flex-shrink:0; width:20px;"></i>
                        <div>
                            <strong>Email</strong><br>
                            <a href="mailto:<?= CONTACT_EMAIL ?>" style="color:var(--primary-pink);">
                                <?= CONTACT_EMAIL ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div style="display:flex; gap:1rem;">
                        <i class="fas fa-clock" style="color:var(--primary-pink); flex-shrink:0; width:20px;"></i>
                        <div>
                            <strong>Horaires</strong><br>
                            Lundi - Samedi : 9h - 18h<br>
                            Dimanche : Fermé
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Social Media -->
            <div class="card">
                <h3 class="mb-3"><i class="fas fa-share-alt"></i> Suivez-nous</h3>
                <div style="display:flex; gap:1rem; font-size:2rem;">
                    <a href="<?= FACEBOOK_URL ?>" 
                       target="_blank" 
                       style="color:var(--primary-pink); transition: transform 0.2s;"
                       onmouseover="this.style.transform='scale(1.2)'"
                       onmouseout="this.style.transform='scale(1)'">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="<?= INSTAGRAM_URL ?>" 
                       target="_blank" 
                       style="color:var(--primary-pink); transition: transform 0.2s;"
                       onmouseover="this.style.transform='scale(1.2)'"
                       onmouseout="this.style.transform='scale(1)'">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/<?= str_replace(['+', ' '], '', WHATSAPP_NUMBER) ?>" 
   class="whatsapp-float" 
   target="_blank"
   aria-label="Contact WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- JavaScript -->
<script src="/assets/js/main.js"></script>
</body>
</html>
