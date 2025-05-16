# Database Seeders

This directory contains the database seeders for the application.

## Available Seeders

-   `MenuSeeder`: Seeds categories, products, variations, and options for the menu
-   `TestOrdersSeeder`: Seeds 400 test orders with random data for performance testing

## How to Use TestOrdersSeeder

The TestOrdersSeeder is designed to help test application performance with a large dataset of orders. It will create 400 random orders spread over the last 90 days, with various statuses, items, and customer details.

**Important**: Before running this seeder, make sure you have seeded the menu items and that you have at least one user in the database.

### Running the TestOrdersSeeder

You can run the TestOrdersSeeder with the following command:

```bash
php artisan db:seed --class=TestOrdersSeeder
```

### Clearing Test Orders

To clear the test orders from the database, you can run:

```bash
php artisan migrate:fresh --seed
```

This will reset the database and re-seed it with the default data (without the test orders).

### Performance Considerations

Running the TestOrdersSeeder may take some time due to the large number of records being created. The seeder uses database transactions for better performance and to ensure data integrity.

When testing with a large dataset, consider the following:

1. Check the pagination performance with many records
2. Test searching and filtering with a large order history
3. Test the performance of order detail loading
4. Test the performance of exporting/printing many orders

The seeder will show a progress bar to track the seeding process.
