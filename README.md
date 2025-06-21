# Commission Calculator

A PHP service that calculates commission fees for deposit and withdraw operations.  
Supports different commission rules for private and business users, with weekly fee-free limits for private withdrawals
and currency conversion handling.

---

## Features

- Calculate commissions for `deposit` and `withdraw` operations
- Business users pay a fixed percentage commission on withdrawals (0.5%)
- Private users get weekly free withdrawal limits (amount and count)
- Converts transaction amounts to EUR for fee calculation and back to original currency
- Rounds commission fees up according to currency precision
- Exception handling for unknown operation types
- Unit tested with PHPUnit

---

## Requirements

- PHP 8.2 or higher
- Composer for dependencies

---

## Installation

Clone the repository and install dependencies:

```bash
git clone <repository-url>
cd commission-calculator
composer install
