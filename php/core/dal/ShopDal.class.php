<?php

namespace ManyThings\Core\Dal;

class ShopDal extends CoreDal
{
    public function GetProductById($productId)
    {
        $sql = 'SELECT p.id, p.merchant_id, p.status, p.time, p.name, p.link, p.description, p.excerpt, p.price, p.price_alt, p.discount, p.delivery, p.delivery_price, url, pp.photo AS main_photo
                FROM products p, products_photos pp
                WHERE p.main_photo_id = pp.id
                  AND p.id = ' . $productId;

        return $this->SqlGetRow($sql);
    }

    public function GetProductByLink($productLink)
    {
        $sql = "SELECT p.id, p.merchant_id, p.status, p.time, p.name, p.link, p.description, p.excerpt, p.price, p.price_alt, p.discount, p.delivery, p.delivery_price, url, pp.photo AS main_photo
                FROM products p, products_photos pp
                WHERE p.main_photo_id = pp.id
                  AND p.link = '" . $productLink . "'";

        return $this->SqlGetRow($sql);
    }

    public function GetProductPrice($productId)
    {
        $sql = 'SELECT price
                FROM products
                WHERE id = ' . $productId;

        return $this->SqlGetVar($sql);
    }

    public function AddCart($req)
    {
        $sql = "INSERT INTO carts (
                    sid,
                    user_id,
                    email,
                    status,
                    time,
                    ip,
                    price,
                    coupon,
                    payment_method,
                    tpv_result
                )
                VALUES (
                    '" . $req['sid'] . "',
                    " . $req['user_id'] . ",
                    '" . addslashes($req['email']) . "',
                    " . $req['status'] . ',
                    ' . $req['time'] . ",
                    '" . $req['ip'] . "',
                    " . $req['price'] . ",
                    '" . addslashes($req['coupon']) . "',
                    '" . addslashes($req['payment_method']) . "',
                    '" . addslashes($req['tpv_result']) . "'
                )";

        return $this->SqlInsert($sql);
    }

    public function EditCart($cartId, $req)
    {
        $sql = 'UPDATE carts
    		SET
                    id = ' . $cartId;
        $sql .= (isset($req['user_id'])) ? ', user_id = ' . $req['user_id'] : '';
        $sql .= (isset($req['email'])) ? ", email = '" . addslashes($req['email']) . "'" : '';
        $sql .= (isset($req['status'])) ? ', status = ' . $req['status'] : '';
        $sql .= (isset($req['price'])) ? ', price = ' . $req['price'] : '';
        $sql .= (isset($req['coupon'])) ? ", coupon = '" . addslashes($req['coupon']) . "'" : '';
        $sql .= (isset($req['payment_method'])) ? ", payment_method = '" . addslashes($req['payment_method']) . "'" : '';
        $sql .= (isset($req['tpv_result'])) ? ", tpv_result = '" . addslashes($req['tpv_result']) . "'" : '';
        $sql .= ' WHERE id = ' . $cartId;

        return $this->SqlUpdate($sql);
    }

    public function SetCartUser($cartId, $userId)
    {
        $sql = 'UPDATE carts
                SET
                    user_id = ' . $userId . '
                WHERE id = ' . $cartId;

        return $this->SqlUpdate($sql);
    }

    public function SetCartEmail($cartId, $email)
    {
        $sql = "UPDATE carts
                SET
                    email = '" . addslashes($email) . "'
                WHERE id = " . $cartId;

        return $this->SqlUpdate($sql);
    }

    public function SetCartStatus($cartId, $status)
    {
        $sql = 'UPDATE carts
                SET
                    status = ' . $status . '
                WHERE id = ' . $cartId;

        return $this->SqlUpdate($sql);
    }

    public function SetCartPrice($cartId, $price)
    {
        $sql = 'UPDATE carts
                SET
                    price = ' . $price . '
                WHERE id = ' . $cartId;

        return $this->SqlUpdate($sql);
    }

    public function SetCartCoupon($cartId, $coupon)
    {
        $sql = "UPDATE carts
                SET
                    coupon = '" . addslashes($coupon) . "'
                WHERE id = " . $cartId;

        return $this->SqlUpdate($sql);
    }

    public function SetCartPaymentMethod($cartId, $paymentMethod)
    {
        $sql = "UPDATE carts
                SET
                    payment_method = '" . addslashes($paymentMethod) . "'
                WHERE id = " . $cartId;

        return $this->SqlUpdate($sql);
    }

    public function SetCartTpvResult($cartId, $tpvResult)
    {
        $sql = "UPDATE carts
                SET
                    tpv_result = '" . addslashes($tpvResult) . "'
                WHERE id = " . $cartId;

        return $this->SqlUpdate($sql);
    }

    public function SetCartProductStatus($cartId, $productId, $status)
    {
        $sql = 'UPDATE carts_products
                SET
                    status = ' . $status . '
                WHERE cart_id = ' . $cartId . '
                  AND product_id = ' . $productId;

        return $this->SqlUpdate($sql);
    }

    public function SetCartProductPrice($cartId, $productId, $price)
    {
        $sql = 'UPDATE carts_products
                SET
                    price = ' . $price . '
                WHERE cart_id = ' . $cartId . '
                  AND product_id = ' . $productId;

        return $this->SqlUpdate($sql);
    }

    public function GetCartById($cartId)
    {
        $sql = 'SELECT id, sid, user_id, email, status, time, ip, price, coupon, payment_method, tpv_result
                FROM carts
                WHERE id = ' . $cartId;

        return $this->SqlGetRow($sql);
    }

    public function GetCartProducts($cartId)
    {
        $sql = 'SELECT p.id, cp.price, cp.time, p.status, p.name, p.link, p.description, p.excerpt, pp.photo AS main_photo
                FROM carts_products cp, products p, products_photos pp
                WHERE cp.product_id = p.id
                  AND p.main_photo_id = pp.id
                  AND cp.cart_id = ' . $cartId;

        return $this->SqlGetResults($sql);
    }

    public function GetCartProduct($cartId, $productId)
    {
        $sql = 'SELECT p.id, cp.price, cp.time, p.status, p.name, p.link, p.description, p.excerpt, pp.photo AS main_photo
                FROM carts_products cp, products p, products_photos pp
                WHERE cp.product_id = p.id
                  AND p.main_photo_id = pp.id
                  AND cp.cart_id = ' . $cartId . '
                  AND cp.product_id = ' . $productId;

        return $this->SqlGetRow($sql);
    }

    public function GetSessionCartId($sid)
    {
        $sql = "SELECT id
                FROM carts
                WHERE sid = '" . $sid . "'
                ORDER BY time DESC
                LIMIT 1";

        return $this->SqlGetVar($sql);
    }

    public function GetSessionCarts($sid)
    {
        $sql = "SELECT id
                FROM carts
                WHERE sid = '" . $sid . "'";

        return $this->SqlGetResults($sql);
    }

    public function DeleteSessionCart($cartId)
    {
        $sql = 'DELETE FROM carts
    		WHERE id = ' . $cartId;

        return $this->SqlDelete($sql);
    }

    public function DeleteSessionCartProducts($cartId)
    {
        $sql = 'DELETE FROM carts_products
    		WHERE cart_id = ' . $cartId;

        return $this->SqlDelete($sql);
    }

    public function AddCartProduct($req)
    {
        $sql = 'INSERT INTO carts_products (
                    cart_id,
                    product_id,
                    price,
                    status,
                    time
                )
                VALUES (
                    ' . $req['cart_id'] . ',
                    ' . $req['product_id'] . ',
                    ' . $req['price'] . ',
                    ' . $req['status'] . ',
                    ' . $req['time'] . '
                )';

        return $this->SqlInsert($sql);
    }

    public function AddOrder($req)
    {
        $sql = 'INSERT INTO orders (
                    cart_id,
                    user_id,
                    email,
                    status,
                    time,
                    ip,
                    price,
                    coupon,
                    payment_method
                )
                VALUES (
                    ' . $req['cart_id'] . ',
                    ' . $req['user_id'] . ",
                    '" . addslashes($req['email']) . "',
                    " . $req['status'] . ',
                    ' . $req['time'] . ",
                    '" . $req['ip'] . "',
                    " . $req['price'] . ",
                    '" . addslashes($req['coupon']) . "',
                    '" . $req['payment_method'] . "'
                )";

        return $this->SqlInsert($sql);
    }

    public function SetOrderRef($orderId, $ref)
    {
        $sql = "UPDATE orders
                SET
                    ref = '" . $ref . "'
                WHERE id = " . $orderId;

        return $this->SqlUpdate($sql);
    }

    public function SetOrderStatus($orderId, $status)
    {
        $sql = 'UPDATE orders
                SET
                    status = ' . $status . '
                WHERE id = ' . $orderId;

        return $this->SqlUpdate($sql);
    }

    public function AddOrderProduct($req)
    {
        $sql = 'INSERT INTO orders_products (
                    order_id,
                    product_id,
                    price,
                    status,
                    time
                )
                VALUES (
                    ' . $req['order_id'] . ',
                    ' . $req['product_id'] . ',
                    ' . $req['price'] . ',
                    ' . $req['status'] . ',
                    ' . $req['time'] . '
                )';

        return $this->SqlInsert($sql);
    }

    public function GetOrderById($orderId)
    {
        $sql = 'SELECT o.id, o.ref, o.cart_id, o.user_id, u.username, u.link AS userlink, o.email, o.status, o.time, o.ip, o.price, o.coupon, o.payment_method
                FROM orders o LEFT OUTER JOIN users u ON o.user_id = u.id
                WHERE o.id = ' . $orderId;

        return $this->SqlGetRow($sql);
    }

    public function GetOrderByCartId($cartId)
    {
        $sql = 'SELECT o.id, o.ref, o.cart_id, o.user_id, u.username, u.link AS userlink, o.email, o.status, o.time, o.ip, o.price, o.coupon, o.payment_method
                FROM orders o LEFT OUTER JOIN users u ON o.user_id = u.id
                WHERE o.cart_id = ' . $cartId;

        return $this->SqlGetRow($sql);
    }

    public function GetOrderProducts($orderId)
    {
        $sql = 'SELECT p.id, op.price, op.time, p.status, p.name, p.link, p.description, p.excerpt, pp.photo AS main_photo
                FROM orders_products op, products p, products_photos pp
                WHERE op.product_id = p.id
                  AND p.main_photo_id = pp.id
                  AND op.order_id = ' . $orderId;

        return $this->SqlGetResults($sql);
    }

    public function GetUserOrders($userId)
    {
        $sql = 'SELECT o.id, o.ref, o.cart_id, o.user_id, u.username, o.email, o.status, o.time, o.ip, o.price, o.coupon, o.payment_method
                FROM orders o LEFT OUTER JOIN users u ON o.user_id = u.id
                WHERE o.user_id = ' . $userId;

        return $this->SqlGetResults($sql);
    }

    public function AddCoupon($req)
    {
        $sql = "INSERT INTO coupons (
                    ref,
                    status,
                    name,
                    coupon_type,
                    product_id,
                    discount_type,
                    discount_amount,
                    uses_per_coupon,
                    uses_per_user,
                    time_from,
                    time_to
                )
                VALUES (
                    '" . $req['ref'] . "',
                    " . $req['status'] . ",
                    '" . $req['name'] . "',
                    " . $req['coupon_type'] . ',
                    ' . $req['product_id'] . ',
                    ' . $req['discount_type'] . ',
                    ' . $req['discount_amount'] . ',
                    ' . $req['uses_per_coupon'] . ',
                    ' . $req['uses_per_user'] . ',
                    ' . $req['time_from'] . ',
                    ' . $req['time_to'] . '
                )';

        return $this->SqlInsert($sql);
    }

    public function GetCouponById($couponId)
    {
        $sql = 'SELECT c.id, c.ref, c.status, c.name, c.coupon_type, c.product_id, p.name AS product_name, c.discount_type, c.discount_amount, c.uses_per_coupon, c.uses_per_user, c.time_from, c.time_to
                FROM coupons c LEFT OUTER JOIN products p ON c.product_id = p.id
                WHERE c.id = ' . $couponId;

        return $this->SqlGetRow($sql);
    }

    public function GetCouponByRef($ref)
    {
        $sql = "SELECT c.id, c.ref, c.status, c.name, c.coupon_type, c.product_id, p.name AS product_name, c.discount_type, c.discount_amount, c.uses_per_coupon, c.uses_per_user, c.time_from, c.time_to
                FROM coupons c LEFT OUTER JOIN products p ON c.product_id = p.id
                WHERE c.ref = '" . $ref . "'";

        return $this->SqlGetRow($sql);
    }

    public function SetCouponStatus($couponId, $status)
    {
        $sql = 'UPDATE coupons
                SET
                    status = ' . $status . '
                WHERE id = ' . $couponId;

        return $this->SqlUpdate($sql);
    }
}
