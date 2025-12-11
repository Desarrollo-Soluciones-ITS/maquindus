## Deploy Laravel app to Windows Server and IIS

Deploying a PHP Laravel application on a Windows Server with IIS involves several steps to ensure the web server handles PHP requests correctly.

Here’s a step-by-step guide to get you started with setting up PHP and Laravel on IIS.

## Step 1: Download PHP for Windows

First, download the latest version of PHP for Windows from the official PHP website .

Choose the “Non-thread-safe” version for better compatibility with IIS FastCGI.

## Step 2: Install PHP on the Server

1. Extract the downloaded PHP files to a directory, for example, `C:\PHP`.
2. Add `C:\PHP` to your system’s Environment Variables to make PHP globally accessible:
   — Right-click on This PC and select Properties.
   — Click Advanced system settings → Environment Variables.
   — Under System Variables, find Path, click Edit, and add the PHP directory path (`C:\PHP`).
3. Open Command Prompt and type `php -v` to verify that PHP is installed and properly configured.

## Step 3: Configure IIS for PHP

1. Open IIS Manager
2. Create a new site (do not set it up as a sub-application):
   — Select Sites in the left pane and click Add Website.
   — Point the Physical path to your Laravel application’s folder.
   — Assign a domain (or use localhost for testing purposes).

## Step 4: Install FastCGI for IIS

PHP works with IIS through the FastCGI module, so you need to ensure it’s installed:

1. Open Server Manager → Add Roles and Features.
2. In the Features section, make sure CGI is installed. If it isn’t, check the box and install it.

## Step 5: Set Up Handler Mapping for PHP in IIS

1. In IIS Manager, select the site you just created.
2. Under Handler Mappings, click Add Module Mapping .
3. Fill in the form as follows:
   — Request path: `*.php`
   — Module: FastCGI Module
   — Executable: Path to `php-cgi.exe` (e.g., `C:\PHP\php-cgi.exe`)
   — Name: `PHP_FastCGI`
4. Click OK, then confirm if prompted.

## Step 6: Install IIS Rewrite and CORS Modules

To ensure your Laravel app functions correctly with URL rewriting and CORS, you need to install the IIS Rewrite and CORS modules:
– Download and install the URL Rewrite module and CORS module

## Step 7: Configure `php.ini`

Locate the `php.ini` file in your `C:\PHP` folder.
Modify the following settings to optimize PHP in your php.ini file

```ini
max_execution_time = 90
max_input_time = 90
memory_limit = 4028M
post_max_size = 64M
```

3. Ensure the following extensions are enabled by uncommenting them or adding them if missing:

```ini
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=php_gd.dll
extension=D:\PHP\ext\php_mysqli.dll
extension=php_sqlsrv_81_nts_x64.dll
extension=php_pdo_sqlsrv_81_nts_x64.dll
```

Note: If you’re using a different version of PHP, replace `81` in the SQL Server drivers with your version number (e.g., `82`).

## Step 8: Install PHP SQL Server Drivers

Download the appropriate version of the PHP SQL Server drivers from this link for your PHP version (e.g., `php_sqlsrv_82_nts_x64.dll` for PHP 8.2). 2. Copy the downloaded `.dll` files to the `ext` folder inside `C:\PHP`. 3. In `php.ini`, ensure the SQLSRV and PDO_SQLSRV extensions are enabled:

```ini
extension=php_sqlsrv_81_nts_x64.dll
extension=php_pdo_sqlsrv_81_nts_x64.dll
```

## Step 9: Restart IIS

After all configurations, restart IIS to apply the changes:
– Open Command Prompt as Administrator and run:

iisreset

## Step 10: Test the Laravel Application

1. Navigate to the URL (or localhost) where the application is hosted.
2. If everything is set up correctly, you should see your Laravel application’s homepage.

## Conclusion

By following these steps, you can successfully set up PHP Laravel on a Windows Server with IIS. This setup ensures that Laravel runs efficiently with proper PHP, SQL Server drivers, and IIS FastCGI support. Don’t forget to enable necessary PHP extensions and tweak the `php.ini` file to match your application’s requirements.
