-- Migration: Add client authentication and e-commerce features
-- Date: 2025-11-10

-- Add password column to client table
ALTER TABLE `client` ADD COLUMN `mdp` VARCHAR(255) DEFAULT NULL AFTER `email`;

-- Create cart table for persistent shopping carts
CREATE TABLE IF NOT EXISTS `panier` (
  `id_panier` INT(11) NOT NULL AUTO_INCREMENT,
  `id_client` INT(11) NOT NULL,
  `num_pr` VARCHAR(255) NOT NULL,
  `qte` INT(11) NOT NULL DEFAULT 1,
  `date_ajout` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_panier`),
  FOREIGN KEY (`id_client`) REFERENCES `client`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`num_pr`) REFERENCES `produit`(`num_pr`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create order status/payment info extension for commande table
ALTER TABLE `commande` 
  ADD COLUMN `adr_livraison` VARCHAR(1000) DEFAULT NULL AFTER `id_cli`,
  ADD COLUMN `mode_paiement` ENUM('card', 'on_arrival') DEFAULT 'on_arrival' AFTER `adr_livraison`,
  ADD COLUMN `statut` ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending' AFTER `mode_paiement`,
  ADD COLUMN `total_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `statut`;

-- Add index for client orders
ALTER TABLE `commande` ADD INDEX `idx_client_orders` (`id_cli`, `date_com`);
