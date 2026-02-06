## 🧠 Task: Discount calculation system for an online store

### 🎯 Goal

Implement a **service class** that calculates the **final order amount** taking into account:

* number of products
* total amount
* user type
* coupon
* delivery

---

## 📦 Input data (Order)

There is an order with the following data:

```php
$orderData = [
'items' => [
['price' => 1200, 'qty' => 2],
['price' => 500, 'qty' => 1],
],
'user_type' => 'regular', // guest | regular | vip
'coupon' => 'SALE10', // null or code
'delivery_type' => 'courier', // pickup | courier
];
```

---

## 📐 Calculation rules

### 1️⃣ Base amount

```
total = sum(price * qty)
```

---

### 2️⃣ Discount by user type

| Type | Discount |
| ------- | ------ |
| guest | 0% |
| regular | 5% |
| vip | 10% |

---

### 3️⃣ Coupons

| Coupon | Condition | Discount |
| ------- | ----------------- | ------- |
| SALE10 | if total ≥ 1000 | −10% |
| FIXED50 | if total ≥ 500 | −50 UAH |

⚠️ **Coupon does not work if the condition is not met**

---

### 4️⃣ Delivery

| Type | Cost |
| ------- | ---------------------------------- |
| pickup | 0 |
| courier | 80 UAH |
| courier | **free**, if total ≥ 2000 |

---

### 5️⃣ Limitations

* Maximum total discount — **30%**
* Final amount **cannot be < 0**

---

## 🏗️ Architectural requirements

### ✅ Required:

* **OrderCalculatorService**
* individual methods:

* `calculateBaseTotal()`
* `applyUserDiscount()`
* `applyCoupon()`
* `calculateDeliveryCost()`
* `getFinalTotal()`

📁 Place the service in:

```
app/Services/OrderCalculatorService.php
```

---

## 🔢 Expected result

```php
$calculator = new OrderCalculatorService($orderData);

$result = $calculator->getFinalTotal();

/*
[
'base_total' => 2900,
'discount' => 580,
'delivery' => 0,
'final_total' => 2320,
]
*/
```

---

## 🔥 Additional complications (optional)

1. Use **Enum** for:
* user type
* delivery type
2. Add **validation** of input data
3. Cover the service with **Pest-tests**
4. Make a `DiscountStrategy` (Strategy pattern)

---
