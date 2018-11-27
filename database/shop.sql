CREATE TABLE `carts` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `sid` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `time` int(10) UNSIGNED NOT NULL,
  `ip` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,0) UNSIGNED NOT NULL,
  `coupon` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `payment_method` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `tpv_result` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `carts`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE `carts_products` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `cart_id` mediumint(8) UNSIGNED NOT NULL,
  `product_id` mediumint(8) UNSIGNED NOT NULL,
  `price` decimal(10,0) UNSIGNED NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `time` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `carts_products`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `carts_products`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE `coupons` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `ref` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL COMMENT '0 (nulled), 1 (pending), 2 (used)',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `coupon_type` tinyint(3) UNSIGNED NOT NULL COMMENT '1 (product), 2 (cart), 3 (shipping)',
  `product_id` mediumint(8) UNSIGNED NOT NULL,
  `discount_type` tinyint(3) UNSIGNED NOT NULL COMMENT '1 (percentage), 2 (quantity), 3 (free)',
  `discount_amount` decimal(10,0) UNSIGNED NOT NULL,
  `uses_per_coupon` tinyint(3) UNSIGNED NOT NULL,
  `uses_per_user` tinyint(3) UNSIGNED NOT NULL,
  `time_from` int(10) UNSIGNED NOT NULL,
  `time_to` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `coupons`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE `orders` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `ref` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `cart_id` mediumint(8) UNSIGNED NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `time` int(10) UNSIGNED NOT NULL,
  `ip` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,0) UNSIGNED NOT NULL,
  `coupon` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `payment_method` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `orders`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE `orders_products` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `order_id` mediumint(8) UNSIGNED NOT NULL,
  `product_id` mediumint(8) UNSIGNED NOT NULL,
  `price` decimal(10,0) UNSIGNED NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `time` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `orders_products`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `orders_products`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE `products` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `merchant_id` mediumint(8) UNSIGNED NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `time` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8_unicode_ci NOT NULL,
  `main_photo_id` mediumint(8) UNSIGNED NOT NULL,
  `price` decimal(10,0) UNSIGNED NOT NULL,
  `price_alt` decimal(10,0) UNSIGNED NOT NULL,
  `discount` tinyint(3) UNSIGNED NOT NULL,
  `delivery` tinyint(3) UNSIGNED NOT NULL,
  `delivery_price` decimal(10,0) UNSIGNED NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `products`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE `products_photos` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `product_id` mediumint(8) UNSIGNED NOT NULL,
  `photo` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `products_photos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `products_photos`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE `provinces` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prefix` varchar(2) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `provinces`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `provinces`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;