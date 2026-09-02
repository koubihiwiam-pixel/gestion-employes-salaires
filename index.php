<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'accueil - FST ProGestion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa; /* Light background */
            color: #495057; /* Dark text for better readability */
            margin: 0;
        }

        /* Welcome Section */
        .welcome-section {
           background: linear-gradient(#cfd9e2, #2C3E50 ); /* Dark gradient */
            color: #000;
            text-align: center;
            padding: 100px 20px;
            position: relative;
            border-bottom: 2px solid #ecf0f1;
        }

        .welcome-section h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .welcome-section p {
            font-size: 1.25rem;
        }

        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            max-width: 150px;
        }

        /* Align the button to the right */
        .btn-primary {
            padding: 12px 25px;
            background-color: #c78bc2; /* New color for the button - dark blue */
            border: none;
            border-radius: 30px;
            font-size: 1.1rem;
            transition: background-color 0.3s;
            position: absolute;
            right: 20px; /* Align button to the right */
            bottom: 30px;
        }

        .btn-primary:hover {
            background-color: #5DADE2; /* Light blue color on hover */
        }

        /* Mission Section */
        .mission-section {
            background-color: white;
            margin-top: -50px;
            padding: 50px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: 0 auto;
        }

        .mission-section h2 {
            font-size: 2.5rem;
            margin-bottom: 30px;
        }

        .mission-section p {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .carousel-item img {
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        /* Footer Section */
        .footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 30px 20px;
        }

        .footer p {
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .footer a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            margin: 0 15px;
        }

        .footer a:hover {
            color: #3498db;
        }

        /* Carousel Navigation */
        .carousel-control-prev-icon, .carousel-control-next-icon {
            background-color: #3498db;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .welcome-section h1 {
                font-size: 2.5rem;
            }

            .welcome-section p {
                font-size: 1rem;
            }

            .mission-section {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

<!-- Welcome Section -->
<div class="welcome-section">
    <img src="logo/img.png" alt="FST ProGestion Logo" class="logo">
    <h1><strong>FST ProGestion</strong></h1>
    <p><strong>Votre plateforme de gestion des employés pour une meilleure efficacité et organisation.</strong></p>
    <a href="login.php" class="btn btn-primary">Mon espace</a>
</div>

<!-- Mission Section -->
<div class="mission-section">
    <h2>Notre Mission</h2>
    <p>Nous nous engageons à simplifier la gestion des ressources humaines et à améliorer l'expérience de travail de nos employés. 
    Grâce à notre plateforme, tout devient plus rapide, plus simple et plus efficace. Nous fournissons des outils modernes pour une gestion optimisée.</p>

    <!-- Carousel Section -->
    <div id="carouselPhotos" class="carousel slide mt-5" data-bs-ride="carousel" data-bs-interval="3000" style="max-width: 800px; margin: 0 auto;">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="image/photo2.jpg" class="d-block w-100 rounded" alt="Photo 2">
            </div>
            <div class="carousel-item">
                <img src="image/photo3.webp" class="d-block w-100 rounded" alt="Photo 3">
            </div>
            <div class="carousel-item">
                <img src="image/photo4.jpg" class="d-block w-100 rounded" alt="Photo 4">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselPhotos" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Précédent</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselPhotos" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Suivant</span>
        </button>
    </div>
</div>

<!-- Footer Section -->
<footer class="footer">
    <p>&copy; 2025 FST ProGestion. Tous droits réservés.</p>
    <div>
        <a href="#" id="contactLink">Contact</a>
        <a href="#" id="legalLink">Mentions légales</a>
        <a href="#" id="aboutLink">À propos de nous</a>
        <a href="#" id="privacyLink">Politique de confidentialité</a>
    </div>
     <!-- Section infos dynamiques -->
  <div id="infoSection" class="text-start mt-4" style="max-width: 900px; margin: 0 auto; display: none;">
    <div class="contact-info d-none">
      <p><strong>Nom de l’entreprise :</strong> FST ProGestion</p>
      <p><strong>Adresse :</strong> Faculté des Sciences et Techniques, Errachidia, Maroc</p>
      <p><strong>Téléphone :</strong> +212 6 25 18 88 01</p>
      <p><strong>Email :</strong> <a href="mailto:contact@fstprogestion.com" class="text-white">contact@fstprogestion.com</a></p>
    </div>

    <div class="legal-info d-none">
      <h5>Mentions légales</h5>
      <p>Les informations présentées sur ce site sont destinées à un usage interne. Le respect des lois en vigueur est obligatoire.</p>
    </div>

    <div class="about-info d-none">
      <h5>À propos de nous</h5>
      <p>FST ProGestion est une solution de gestion RH moderne, dédiée aux établissements d'enseignement et entreprises.</p>
    </div>

    <div class="privacy-info d-none">
      <h5>Politique de confidentialité</h5>
      <p>Les données personnelles sont protégées et utilisées exclusivement pour la gestion RH. Aucune donnée n'est partagée sans consentement.</p>
    </div>
  </div>

</footer>

<script>
    // JavaScript for the Footer Links to show dynamic content (Optional)
    function showInfo(sectionClass) {
        const infoSection = document.getElementById('infoSection');
        infoSection.style.display = 'block';
        infoSection.querySelectorAll('div').forEach(div => {
            div.classList.add('d-none');
        });
        const target = infoSection.querySelector(sectionClass);
        if (target) target.classList.remove('d-none');
    }

    document.getElementById('contactLink').addEventListener('click', function(e) {
        e.preventDefault();
        showInfo('.contact-info');
    });

    document.getElementById('legalLink').addEventListener('click', function(e) {
        e.preventDefault();
        showInfo('.legal-info');
    });

    document.getElementById('aboutLink').addEventListener('click', function(e) {
        e.preventDefault();
        showInfo('.about-info');
    });

    document.getElementById('privacyLink').addEventListener('click', function(e) {
        e.preventDefault();
        showInfo('.privacy-info');
    });
</script>

</body>
</html>
