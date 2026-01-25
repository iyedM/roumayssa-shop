-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Hôte : sql102.infinityfree.com
-- Généré le :  Dim 25 jan. 2026 à 08:13
-- Version du serveur :  11.4.9-MariaDB
-- Version de PHP :  7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `if0_40985360_roumayssa`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id`, `first_name`, `last_name`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Test', 'Admin', 'testadmin', 'test@roumayssa.com', '$2y$12$PP/qWzcABbY4ajf7ZQe7u.KcKMZD8TVz5/hv6whOAO4t1/D4O95Sm', '2026-01-21 16:36:57');

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(17, 'Robes', NULL, '2026-01-24 23:58:26'),
(18, 'Manteaux', NULL, '2026-01-24 23:58:37'),
(19, 'Doudounes', NULL, '2026-01-24 23:58:41'),
(20, 'Cuir', NULL, '2026-01-24 23:58:48'),
(21, 'Blazers', NULL, '2026-01-24 23:58:52'),
(22, 'Pantalons', NULL, '2026-01-24 23:59:28'),
(23, 'Accessoires', NULL, '2026-01-24 23:59:33'),
(24, 'Culottes', NULL, '2026-01-25 00:00:20');

-- --------------------------------------------------------

--
-- Structure de la table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `full_name`, `email`, `phone`, `message`, `lu`, `created_at`) VALUES
(2, 'iyed iyed', 'iyed.mohamed@isimg.tn', '53039985', 'adad rgeerv grgev', 1, '2026-01-23 23:13:14'),
(3, 'iyed iyed', 'iyed.mohamed@isimg.tn', '53039985', 'hfgyt tdytid rtyd yt', 1, '2026-01-24 20:26:11'),
(4, 'Eline Mohamed', 'eline.mohamed@gmail.com', '12345678', 'bonjour je teste le site hebergé', 1, '2026-01-25 00:56:49');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_address` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'nouvelle',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `livraison` tinyint(1) DEFAULT 1 COMMENT '0=retrait magasin, 1=livraison domicile'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `customer_phone`, `customer_address`, `total_price`, `status`, `created_at`, `livraison`) VALUES
(20, 'Julia Mohamed', '12345678', 'zaouia 4213', '1013.00', 'confirmé', '2026-01-25 00:37:01', 1);

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(23, 20, 48, 2, '129.00'),
(24, 20, 46, 1, '219.00'),
(25, 20, 45, 1, '529.00');

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `promo_price` decimal(10,2) DEFAULT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `promo_price`, `purchase_price`, `stock`, `image`, `active`, `created_at`) VALUES
(34, 24, 'BODY À CARREAUX', 'Body avec col droit et bretelles avec fermeture boutonnée sur le devant. Poche plaquée sur la poitrine. Fermeture par boutons-pression sur le devant. Imprimé à carreaux.\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '89.90', NULL, '79.90', 5, 'uploads/product_69755de7b06c0_0_culottes.jpg', 1, '2026-01-25 00:03:52'),
(35, 24, 'BODY EN DENIM DOUX', 'Body en denim avec col droit et bretelles avec fermeture boutonnée sur le devant. Poche plaquée sur le devant. Fermeture par boutons-pression sur le bas.\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '99.90', NULL, '69.90', 4, 'uploads/product_69755e32112c5_0_culottes4.jpg', 1, '2026-01-25 00:05:06'),
(36, 24, 'COMBINAISON À CARREAUX', 'Combinaison avec col en V et manches longues. Finitions en gomme avec décor volant. Fermeture croisée avec bouton caché et lien latéral. Imprimé carreaux.\r\n\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '109.90', NULL, '79.90', 7, 'uploads/product_69755e5f5d403_0_culottes5.jpg', 1, '2026-01-25 00:05:52'),
(37, 24, 'COMBINAISON À CARREAUX', 'Combinaison avec col en V et manches longues. Finitions en gomme avec décor volant. Fermeture croisée avec bouton caché et lien latéral. Imprimé carreaux.\r\n\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '109.90', NULL, '79.90', 7, 'uploads/product_69755f8dd70ba_0_culottes5.jpg', 1, '2026-01-25 00:10:53'),
(38, 17, 'ROBE COURTE À CARREAUX AVEC VOLANTS ET CEINTURE', 'Robe courte à col à revers et manches longues. Taille ajustable avec boucle et élastique au dos. Bas à volants. Fermeture avant par boutons-pression.\r\n\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '199.00', NULL, '169.00', 3, 'uploads/product_6975605822aa7_1769300056.jpg', 1, '2026-01-25 00:11:31'),
(39, 17, 'ROBE MI-LONGUE EN MAILLE COL EN V', 'Robe avec col en V et manches longues avec la taille large se rétrécissant vers le poignet. Taille ajustée. Bas évasé.\r\n\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '229.00', NULL, '169.00', 3, 'uploads/product_69755fdca653a_0_robe21.jpg', 1, '2026-01-25 00:12:12'),
(40, 17, 'ROBE EN MAILLE MANCHES CHAUVE-SOURIS', 'Robe en maille unie avec col montant. Manches longues avec des emmanchures très larges, se resserrant vers le poignet.\r\n\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '199.00', NULL, '149.00', 3, 'uploads/product_697560266e9fb_0_robe31.jpg', 1, '2026-01-25 00:13:26'),
(41, 18, 'DOUDOUNE À CAPUCHE DÉPERLANTE COUPE-VENT', 'Doudoune avec rembourrage de 60% de duvet et 40% de plumes. Tissu coupe-vent et déperlant, empêchant l\'air de pénétrer en cas de vents forts et vous protégeant de la bruine lors de courtes expositions. Ce vêtement offre un confort thermique dans des environnements froids à des températures de référence en fonction du niveau d\'activité, avec 7ºC pour une activité faible et -22ºC pour une activité modérée.\r\n\r\nCol montant avec doublure intérieure et capuche effet fourrure amovible et ajustable avec cordons. Manches longues avec poignets intérieurs. Poches avant avec doublure polaire intérieure et fermeture zippée. Ceinture élastique. Fermeture avant par zip dissimulé et boutons-pression.\r\n\r\nVoir plus\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '469.00', NULL, '400.00', 3, 'uploads/product_6975612e78215_0_mant11.jpg', 1, '2026-01-25 00:17:50'),
(42, 18, 'ANORAK COURT MATELASSÉ À NŒUDS ZW COLLECTION', 'ZARA WOMAN COLLECTION\r\n\r\nAnorak court matelassé avec col montant et manches longues. Poches avant. Ourlet droit. Fermeture avant avec boutons dissimulés et avec des nœuds dans le même tissu.\r\n\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '319.00', NULL, '289.00', 3, 'uploads/product_697561a537a6d_0_mant21.jpg', 1, '2026-01-25 00:19:49'),
(44, 20, 'BLOUSON BOMBER 100% CUIR', 'Blouson style bomber avec tissu principal en 100% cuir. Col à revers et manches longues. Poches avant plaquées avec rabat et bouton-pression. Fermeture avant avec zip dissimulé et boutons-pression.\r\n\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '529.00', NULL, '489.00', 5, 'uploads/product_6975623b725a0_0_cuir11.jpg', 1, '2026-01-25 00:22:19'),
(45, 20, 'BLOUSON BOMBER 100% CUIR', 'Blouson style bomber confectionné en 100% cuir. Col à revers et manches longues. Poches avant plaquées avec rabat et bouton-pression. Fermeture avant avec zip dissimulé et boutons-pression.\r\n\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '529.00', NULL, '489.00', 2, 'uploads/product_6975628f1f183_0_cuir21.jpg', 1, '2026-01-25 00:23:43'),
(46, 21, 'BLAZER OVERSIZE ZW COLLECTION', 'ZARA WOMAN COLLECTION\r\n\r\nVeste ample avec col à revers crantés et manches longues. Poches à rabat sur le devant. Fermeture avant croisée avec boutons.\r\n\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '369.00', '219.00', '300.00', 7, 'uploads/product_697562f30ab30_0_blazer11.jpg', 1, '2026-01-25 00:25:23'),
(47, 22, 'PANTALON EN LAINE 100% ZW COLLECTION', 'ZARA WOMAN COLLECTION\r\n\r\nPantalon avec tissu principal confectionné en filature de laine 100%. Taille moyenne et taille avec passants. Poches à l\'avant et au dos. Jambe droite. Fermeture avant avec fermeture éclair, bouton et crochets métalliques.\r\n\r\n\r\nDimensions du produit\r\nComposition & entretien\r\nVoir disponibilité en magasin\r\nLivraison, échange et retours', '319.00', NULL, '280.00', 9, 'uploads/product_69756411a30ba_0_pantalon11.jpg', 1, '2026-01-25 00:30:09'),
(48, 23, 'ÉCHARPE AVEC LAINE ET STRASS', '', '129.00', NULL, '99.00', 13, 'uploads/product_697564690d481_0_acc11.jpg', 1, '2026-01-25 00:31:37');

-- --------------------------------------------------------

--
-- Structure de la table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image`) VALUES
(21, 34, 'uploads/product_69755de7b06c0_0_culottes.jpg'),
(22, 34, 'uploads/product_69755de7b0e04_1_culottes2.jpg'),
(23, 34, 'uploads/product_69755de7b1096_2_culottes3.jpg'),
(24, 35, 'uploads/product_69755e32112c5_0_culottes4.jpg'),
(25, 36, 'uploads/product_69755e5f5d403_0_culottes5.jpg'),
(26, 37, 'uploads/product_69755f8dd70ba_0_culottes5.jpg'),
(27, 39, 'uploads/product_69755fdca653a_0_robe21.jpg'),
(28, 39, 'uploads/product_69755fdca6a55_1_robe22.jpg'),
(29, 40, 'uploads/product_697560266e9fb_0_robe31.jpg'),
(30, 40, 'uploads/product_697560266ef93_1_robe32.jpg'),
(31, 40, 'uploads/product_697560266f1cd_2_robe33.jpg'),
(32, 38, 'uploads/product_6975605822aa7_1769300056.jpg'),
(33, 38, 'uploads/product_6975605822eb9_1769300056.jpg'),
(34, 38, 'uploads/product_697560681428d_1769300072.jpg'),
(35, 38, 'uploads/product_69756068144e7_1769300072.jpg'),
(36, 41, 'uploads/product_6975612e78215_0_mant11.jpg'),
(37, 41, 'uploads/product_6975612e785f8_1_mant12.jpg'),
(38, 41, 'uploads/product_6975612e787ce_2_mant13.jpg'),
(39, 42, 'uploads/product_697561a537a6d_0_mant21.jpg'),
(40, 42, 'uploads/product_697561a538357_1_mant22.jpg'),
(41, 42, 'uploads/product_697561a538636_2_mant23.jpg'),
(45, 44, 'uploads/product_6975623b725a0_0_cuir11.jpg'),
(46, 44, 'uploads/product_6975623b72bcc_1_cuir12.jpg'),
(47, 44, 'uploads/product_6975623b72e54_2_cuir13.jpg'),
(48, 45, 'uploads/product_6975628f1f183_0_cuir21.jpg'),
(49, 45, 'uploads/product_6975628f1f728_1_cuir22.jpg'),
(50, 46, 'uploads/product_697562f30ab30_0_blazer11.jpg'),
(51, 46, 'uploads/product_697562f30affe_1_blazer12.jpg'),
(52, 46, 'uploads/product_697562f30b283_2_blazer13.jpg'),
(53, 47, 'uploads/product_69756411a30ba_0_pantalon11.jpg'),
(54, 47, 'uploads/product_69756411a3420_1_pantalon12.jpg'),
(55, 48, 'uploads/product_697564690d481_0_acc11.jpg'),
(56, 48, 'uploads/product_697564690d782_1_acc12.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `purchase_images`
--

CREATE TABLE `purchase_images` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `site_content`
--

CREATE TABLE `site_content` (
  `id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `content` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `site_content`
--

INSERT INTO `site_content` (`id`, `section`, `content`, `active`, `created_at`, `updated_at`) VALUES
(1, 'home_banner', 'Ceci est un texte de test pour la bannièreaa', 1, '2026-01-21 14:14:46', '2026-01-22 14:21:11');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Index pour la table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `purchase_images`
--
ALTER TABLE `purchase_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_purchase_images` (`purchase_id`);

--
-- Index pour la table `site_content`
--
ALTER TABLE `site_content`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT pour la table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT pour la table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `purchase_images`
--
ALTER TABLE `purchase_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `site_content`
--
ALTER TABLE `site_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `purchase_images`
--
ALTER TABLE `purchase_images`
  ADD CONSTRAINT `fk_purchase_images` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
