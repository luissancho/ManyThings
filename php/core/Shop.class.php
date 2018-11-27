<?php

namespace ManyThings\Core;

use Exception;
use ManyThings\Core\Dal\ShopDal;

class Shop
{
    protected $dal;

    public function __construct()
    {
        $this->dal = new ShopDal();
    }

    public function GetProductById($productId)
    {
        try {
            $product = $this->dal->GetProductById($productId);
        } catch (Exception $e) {
            throw $e;
        }

        return $product;
    }

    public function GetProductByLink($productLink)
    {
        try {
            $product = $this->dal->GetProductByLink($productLink);
        } catch (Exception $e) {
            throw $e;
        }

        return $product;
    }

    public function GetProductOptions($productId)
    {
        try {
            $options = $this->dal->GetProductOptions($productId);
        } catch (Exception $e) {
            throw $e;
        }

        return $options;
    }

    public function GetProductOptionById($optionId)
    {
        try {
            $option = $this->dal->GetProductOptionById($optionId);
        } catch (Exception $e) {
            throw $e;
        }

        return $option;
    }

    public function PurchaseProduct($cartId, $productId, $quantity = 1, $optionId = 0)
    {
        try {
            $product = $this->dal->GetProductById($productId);
            if (empty($product) || $product['status'] < 1) {
                throw new AppException('El producto seleccionado no está disponible.');
            }

            $cart = $this->dal->GetCartById($cartId);

            if ($optionId > 0) {
                $option = $this->dal->GetProductOptionById($optionId);
                $productPrice = $option['price'];
            } else {
                $productPrice = $product['price'];
            }
            $totalPrice = $productPrice * $quantity;

            $cartProduct = $this->dal->GetCartProduct($cartId, $productId);
            if (!empty($cartProduct)) {
                $cartProductId = $cartProduct['id'];

                $price = $cart['price'] - $cartProduct['total'] + $totalPrice;
                $this->dal->SetCartProductTotalPrice($cartId, $productId, $productPrice, $optionId, $quantity, $totalPrice);
                $this->dal->SetCartPrice($cartId, $price);
            } else {
                $data =
                [
                    'cart_id' => $cartId,
                    'product_id' => $productId,
                    'option_id' => $optionId,
                    'price' => $productPrice,
                    'quantity' => $quantity,
                    'total' => $totalPrice,
                    'status' => 1,
                    'time' => time()
                ];
                $cartProductId = $this->dal->AddCartProduct($data);

                $price = $cart['price'] + $totalPrice;
                $this->dal->SetCartPrice($cartId, $price);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $cartProductId;
    }

    public function SetSessionCart()
    {
        $session = DI::getDI()->session;

        $this->dal->DeleteSessionCarts($session->id);

        $userId = ($session->level > 0) ? $session->uid : 0;

        try {
            $data =
            [
                'sid' => $session->id,
                'user_id' => $userId,
                'email' => '',
                'status' => 1,
                'time' => time(),
                'ip' => $session->userIp,
                'price' => 0.0,
                'coupon' => '',
                'payment_method' => '',
                'tpv_result' => ''
            ];
            $cartId = $this->dal->AddCart($data);
        } catch (Exception $e) {
            throw $e;
        }

        return $cartId;
    }

    public function EditCart($cartId, $req)
    {
        try {
            $cart = $this->GetCartById($cartId);

            // Compare data
            $data['user_id'] = (isset($req['user_id']) && $req['user_id'] != $cart['user_id']) ? $req['user_id'] : null;
            $data['email'] = (isset($req['email']) && $req['email'] != $cart['email']) ? $req['email'] : null;
            $data['status'] = (isset($req['status']) && $req['status'] != $cart['status']) ? $req['status'] : null;
            $data['price'] = (isset($req['price']) && $req['price'] != $cart['price']) ? $req['price'] : null;
            $data['coupon'] = (isset($req['coupon']) && $req['coupon'] != $cart['coupon']) ? $req['coupon'] : null;
            $data['payment_method'] = (isset($req['payment_method']) && $req['payment_method'] != $cart['payment_method']) ? $req['payment_method'] : null;
            $data['tpv_result'] = (isset($req['tpv_result']) && $req['tpv_result'] != $cart['tpv_result']) ? $req['tpv_result'] : null;

            $this->dal->EditCart($cart['id'], $data);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function SetCartUser($cartId, $userId)
    {
        try {
            $this->dal->SetCartUser($cartId, $userId);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function SetCartEmail($cartId, $email)
    {
        try {
            $this->dal->SetCartEmail($cartId, $email);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function SetCartStatus($cartId, $status)
    {
        try {
            $this->dal->SetCartStatus($cartId, $status);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function SetCartPrice($cartId, $price)
    {
        try {
            $this->dal->SetCartPrice($cartId, $price);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function SetCartCoupon($cartId, $coupon)
    {
        try {
            $this->dal->SetCartCoupon($cartId, $coupon);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function SetCartPaymentMethod($cartId, $paymentMethod)
    {
        try {
            $this->dal->SetCartPaymentMethod($cartId, $paymentMethod);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function SetCartTpvResult($cartId, $tpvResult)
    {
        try {
            $this->dal->SetCartTpvResult($cartId, $tpvResult);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function GetSessionCartId()
    {
        $session = DI::getDI()->session;

        try {
            $cartId = $this->dal->GetSessionCartId($session->id);
            if (!$cartId) {
                return 0;
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $cartId;
    }

    public function GetSessionCarts()
    {
        $session = DI::getDI()->session;

        try {
            $carts = $this->dal->GetSessionCarts($session->id);
        } catch (Exception $e) {
            throw $e;
        }

        return $carts;
    }

    public function GetSessionCart()
    {
        $session = DI::getDI()->session;

        try {
            $cartId = $this->dal->GetSessionCartId($session->id);
            if (!$cartId) {
                return [];
            }

            $cart = $this->dal->GetCartById($cartId);
        } catch (Exception $e) {
            throw $e;
        }

        return $cart;
    }

    public function GetCartById($cartId)
    {
        try {
            $cart = $this->dal->GetCartById($cartId);
        } catch (Exception $e) {
            throw $e;
        }

        return $cart;
    }

    public function GetCartProducts($cartId)
    {
        try {
            $products = $this->dal->GetCartProducts($cartId);
        } catch (Exception $e) {
            throw $e;
        }

        return $products;
    }

    public function GetCartProduct($cartId, $productId)
    {
        try {
            $product = $this->dal->GetCartProduct($cartId, $productId);
        } catch (Exception $e) {
            throw $e;
        }

        return $product;
    }

    public function SetCartProductStatus($cartId, $productId, $status)
    {
        try {
            $this->dal->SetCartProductStatus($cartId, $productId, $status);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function SetCartProductPrice($cartId, $productId, $price)
    {
        try {
            $this->dal->SetCartProductPrice($cartId, $productId, $price);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function CheckCartCoupon($cartId, $ref)
    {
        try {
            $cart = $this->dal->GetCartById($cartId);
            if ($cart['coupon'] != '') {
                return false;
            }

            $coupon = $this->dal->GetCouponByRef($ref);
            if (empty($coupon) || $coupon['status'] != 1) {
                return false;
            }

            if ($coupon['coupon_type'] == 1) {
                $products = $this->dal->GetCartProducts($cartId);
                foreach ($products as $item) {
                    if ($item['id'] == $coupon['product_id']) {
                        return true;
                    }
                }
            } else {
                return true;
            }
        } catch (Exception $e) {
            throw $e;
        }

        return false;
    }

    public function ApplyCartCoupon($cartId, $ref)
    {
        try {
            if (!$this->CheckCartCoupon($cartId, $ref)) {
                return false;
            }

            $coupon = $this->dal->GetCouponByRef($ref);
            $cart = $this->dal->GetCartById($cartId);

            if ($coupon['coupon_type'] == 1) { // Product
                $price = $cart['price'];

                $products = $this->dal->GetCartProducts($cartId);
                foreach ($products as $item) {
                    if ($item['id'] == $coupon['product_id']) {
                        $productPrice = $item['total'];
                        $price -= $productPrice;

                        if ($coupon['discount_type'] == 1) { // Percent
                            $productPrice -= ($productPrice * $coupon['discount_amount'] / 100);
                        } elseif ($coupon['discount_type'] == 2) { // Quantity
                            $productPrice -= $coupon['discount_amount'];
                        } elseif ($coupon['discount_type'] == 3) { // Free
                            $productPrice = 0;
                        }

                        $this->dal->SetCartProductTotalPrice($cartId, $item['id'], $item['price'], $item['quantity'], $productPrice);

                        $price += $productPrice;
                        $this->dal->SetCartPrice($cartId, $price);

                        $this->dal->SetCartCoupon($cartId, $ref);

                        return true;
                    }
                }
            } elseif ($coupon['coupon_type'] == 2) { // Cart
                $price = $cart['price'];

                if ($coupon['discount_type'] == 1) { // Percent
                    $price -= ($price * $coupon['discount_amount'] / 100);
                } elseif ($coupon['discount_type'] == 2) { // Quantity
                    $price -= $coupon['discount_amount'];
                } elseif ($coupon['discount_type'] == 3) { // Free
                    $price = 0;
                }

                $this->dal->SetCartPrice($cartId, $price);

                $this->dal->SetCartCoupon($cartId, $ref);

                return true;
            } elseif ($coupon['coupon_type'] == 3) { // Ship
                // TODO
            }
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function GetOrderById($orderId)
    {
        try {
            $order = $this->dal->GetOrderById($orderId);
        } catch (Exception $e) {
            throw $e;
        }

        return $order;
    }

    public function GetOrderByCartId($cartId)
    {
        try {
            $order = $this->dal->GetOrderByCartId($cartId);
        } catch (Exception $e) {
            throw $e;
        }

        return $order;
    }

    public function GetOrderProducts($orderId)
    {
        try {
            $products = $this->dal->GetOrderProducts($orderId);
        } catch (Exception $e) {
            throw $e;
        }

        return $products;
    }

    public function GetUserOrders($userId)
    {
        try {
            $orders = $this->dal->GetUserOrders($userId);
        } catch (Exception $e) {
            throw $e;
        }

        return $orders;
    }

    public function AddOrder($req)
    {
        $session = DI::getDI()->session;

        try {
            $data['ref'] = (isset($req['ref'])) ? $req['ref'] : ''; // Optional
            $data['cart_id'] = (isset($req['cart_id'])) ? $req['cart_id'] : 0; // Optional
            $data['user_id'] = $req['user_id']; // Required
            $data['email'] = $req['email']; // Required
            $data['status'] = 1; // Auto
            $data['time'] = (isset($req['time'])) ? $req['time'] : time(); // Optional
            $data['ip'] = (isset($req['ip'])) ? $req['ip'] : $session->userIp; // Optional
            $data['price'] = $req['price']; // Required
            $data['coupon'] = (isset($req['coupon'])) ? $req['coupon'] : ''; // Optional
            $data['payment_method'] = $req['payment_method']; // Required

            $orderId = $this->dal->AddOrder($data);
        } catch (Exception $e) {
            throw $e;
        }

        return $orderId;
    }

    public function AddOrderProduct($req)
    {
        try {
            $data['order_id'] = $req['order_id']; // Required
            $data['product_id'] = $req['product_id']; // Required
            $data['option_id'] = (isset($req['option_id'])) ? $req['option_id'] : 0; // Optional
            $data['price'] = $req['price']; // Required
            $data['quantity'] = $req['quantity']; // Required
            $data['total'] = $req['total']; // Required
            $data['status'] = 1; // Auto
            $data['time'] = (isset($req['time'])) ? $req['time'] : time(); // Optional

            $this->dal->AddOrderProduct($data);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function CreateOrderFromCart($cartId, $paymentMethod = '')
    {
        $session = DI::getDI()->session;

        $time = time();

        try {
            $order = $this->GetOrderByCartId($cartId);
            if (!empty($order)) {
                return 0;
            }

            // Create order
            $cart = $this->dal->GetCartById($cartId);
            $data =
            [
                'ref' => '',
                'cart_id' => $cartId,
                'user_id' => $cart['user_id'],
                'email' => $cart['email'],
                'status' => 1,
                'time' => $time,
                'ip' => $session->userIp,
                'price' => $cart['price'],
                'coupon' => $cart['coupon'],
                'payment_method' => $paymentMethod
            ];
            $orderId = $this->dal->AddOrder($data);

            // Add products purchased to order
            $products = $this->dal->GetCartProducts($cartId);
            foreach ($products as $product) {
                $data =
                [
                    'order_id' => $orderId,
                    'product_id' => $product['id'],
                    'option_id' => $product['option_id'],
                    'price' => $product['price'],
                    'quantity' => $product['quantity'],
                    'total' => $product['total'],
                    'status' => 1,
                    'time' => $time
                ];
                $this->dal->AddOrderProduct($data);
            }

            // Set coupon used
            if ($cart['coupon'] != '') {
                $coupon = $this->GetCouponByRef($cart['coupon']);
                if (!empty($coupon)) {
                    $this->dal->SetCouponStatus($coupon['id'], 2);
                }
            }

            $this->dal->SetOrderRef($orderId, $orderId);
        } catch (Exception $e) {
            throw $e;
        }

        return $orderId;
    }

    public function SetOrderRef($orderId, $ref)
    {
        try {
            $this->dal->SetOrderRef($orderId, $ref);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function SetOrderStatus($orderId, $status)
    {
        try {
            $this->dal->SetOrderStatus($orderId, $status);
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function CreateCoupon($req)
    {
        try {
            $data =
            [
                'ref' => $req['ref'],
                'status' => $req['status'],
                'name' => $req['name'],
                'coupon_type' => $req['coupon_type'],
                'product_id' => $req['product_id'],
                'discount_type' => $req['discount_type'],
                'discount_amount' => $req['discount_amount'],
                'uses_per_coupon' => $req['uses_per_coupon'],
                'uses_per_user' => $req['uses_per_user'],
                'time_from' => $req['time_from'],
                'time_to' => $req['time_to']
            ];
            $couponId = $this->dal->AddCoupon($data);
        } catch (Exception $e) {
            throw $e;
        }

        return $couponId;
    }

    public function GetCouponById($couponId)
    {
        try {
            $coupon = $this->dal->GetCouponById($couponId);
        } catch (Exception $e) {
            throw $e;
        }

        return $coupon;
    }

    public function GetCouponByRef($ref)
    {
        try {
            $coupon = $this->dal->GetCouponByRef($ref);
        } catch (Exception $e) {
            throw $e;
        }

        return $coupon;
    }
}
