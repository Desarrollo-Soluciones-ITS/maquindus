## **Deploying a Laravel app on Windows using IIS**

### **Install IIS**

1. **Open Server Manager**: Start the Server Manager from the Start menu or taskbar.
2. **Add Roles and Features**: Click on "Manage" in the top right corner of the Server Manager, then select "Add Roles and Features".
3. **Role-based or Feature-based Installation**: Choose "Role-based or feature-based installation" and click "Next".
4. **Select the Server**: Select the server on which you want to install IIS and click "Next".
5. **Select Server Roles**: In the roles list, check the box next to "Web Server (IIS)". This action will prompt you to add features that are required for Web Server (IIS); accept these by clicking "Add Features".
6. **Features**: No additional features are required at this point, so you can click "Next".
7. **Web Server Role (IIS)**: Click "Next" on the Web Server Role (IIS) page.
8. **Role Services**: Ensure that the following Role Services are selected:
    - **Web Server**
    - **Common HTTP Features** (Static Content, Default Document)
    - **Application Development** (ISAPI Extensions, ISAPI Filters, CGI)
9. **Confirm Installation Selections**: Review your selections and click "Install".
10. **Complete Installation**: Once the installation is complete, click "Close".

### **Install PHP**

1. **Download PHP**: Go to the [official PHP downloads page](https://windows.php.net/download/) for Windows and download the latest non-thread safe (NTS) Zip file compatible with your server architecture (x64).
2. **Extract PHP**: Extract the downloaded ZIP file to a directory on your server, for example, **C:\\PHP**.
3. **Configure PHP**:
    - **PHP.ini**: Rename the **php.ini-production** file to **php.ini**.
    - Open **php.ini** in a text editor.
    - Configure the following settings:
        - Set **extension_dir** to **"ext"**.
        - Uncomment (remove the **;** at the beginning of the line) any necessary extensions, such as php_curl.dll, hp_gettext.dll, php_mysqli.dll, php_mbstring.dll, php_gd.dll, php_fileinfo.dll, php_pdo_mysql.dll, php_pdo_pgsql.dll, php_pgsql.dll, php_intl.dll

### **Configure IIS to Use PHP**

1. **Open IIS Manager**: Type “Internet Information Services (IIS) Manager” into the start menu and open it.
2. **Add Handler Mapping**:
    - Select your server name in the Connections panel on the left.
    - Double-click on "Handler Mappings".
    - Click "Add Module Mapping" in the right-side Actions panel.
    - Enter the following details:
        - **Request path**: **\*.php**
        - **Module**: **FastCgiModule**
        - **Executable**: **C:\\PHP\\php-cgi.exe** (Adjust the path based on where you extracted PHP.)
        - **Name**: **PHP via FastCGI**
    - Click "OK".
3. **Open Default Documents**: In the middle pane, double-click on the “Default Document” feature. This feature manages the list of default documents for the site or server.
    - **Add index.php to the List**
        - **Add New Document**:
            1. In the right pane, click “Add…” in the Actions menu.
            2. In the “Name” field that appears, enter **index.php**.
            3. Click OK to add it to the list of default documents.
        - **Arrange Document Priority**
            1. **Modify the Order** (if necessary): If **index.php** should be the first document the server looks for (common in PHP applications), you can select it in the list and use the “Move Up” button in the right pane to move it to the top of the list.
    - **Edit Feature Permissions**:
        - Select the PHP handler you just created and click "Edit Feature Permissions" in the right-side Actions panel.
        - Check "Execute", then click "OK".

### **Adding PHP to PATH**

**Locate Your PHP Installation:**

Ensure you know the full path to your PHP installation. If you followed the steps in the previous response, it might be something like **C:\PHP**

**Open System Properties:**

Right-click on the Start menu and select System.

Click on About at the bottom of the settings page.

Click on System info under Related settings.

In the window that opens, click on Advanced system settings on the left sidebar.

**Environment Variables:**

In the System Properties window, go to the Advanced tab and click on Environment Variables.

**Update** **PATH Variable:**

Under the System variables section, scroll and find the Path variable, then click Edit.

In the Edit Environment Variable window, click New and enter the path to your PHP directory (e.g., C:\\PHP).

Click OK to close each dialog.

**Check the Installation:**

Open a Command Prompt (you can do this by typing cmd in the Start menu search).

Type php -v and press Enter. This command checks the PHP version installed. If the PATH was set correctly, it should display the PHP version without any errors.

### **URL Rewrite Module**

**Go to the Official Download Page**: Microsoft provides the URL Rewrite Module on their official IIS website. Visit the [IIS URL Rewrite](https://www.iis.net/downloads/microsoft/url-rewrite) page.

**Select the Correct Version**: Download the version appropriate for your server’s architecture (64-bit). This information can usually be found in your server's System Properties under System Type.

**Install the Module**

**Run the Installer**: Once downloaded, run the installer. This is typically an **.msi** file.

**Follow the Installation Wizard**: Proceed through the installer’s steps. This will usually involve:

- - Accepting the license terms.
    - Choosing a destination folder (default should generally be fine).
    - Completing the installation.

**Verify Installation in IIS Manager**

**Open IIS Manager**: You can open IIS Manager by typing “Internet Information Services (IIS) Manager” in the Start menu.

**Check for URL Rewrite Module**:

- - In the IIS Manager, select your server in the Connections panel on the left.
    - In the main panel, double-click on the “URL Rewrite” icon. If you do not see this icon, the module may not have been installed correctly.

### **Deploy the application**

1. **Copy application files**:
    - Place your Laravel project in a directory within the IIS web root or any directory of your choice if configuring a new website.
    - The common location is **C:\\inetpub\\wwwroot\\**, but you can place it in a separate folder if you prefer.
2. **Set Up IIS Web Site or Modify**:

**New site:**

- 1. In IIS Manager, right-click "Sites" in the Connections pane and choose "Add Website".
        2.  Enter a friendly name for your site in the "Site name" field.
        3.  Set the "Physical path" to the **public** directory of your laravel application.
        4.  Specify the "Binding" settings (e.g., type, IP address, port, and hostname).
        5.  Click "OK" to create the site.

**Existing site:**

- 1. In IIS Manager, right-click "Default Web Sites" in the Connections pane and modify accordingly the physical path to the public directory of your Laravel project.

1. **Configure the web.config File:**
    - Navigate to the **public** directory of your Laravel application.
    - If not already present, create a **web.config** file with URL rewrite rules add the below rewrite rule.
```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Laravel Web Rule" stopProcessing="true">
                    <match url="^" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" />
                </rule>
            </rules>
        </rewrite>
        <defaultDocument>
            <files>
                <add value="index.php" />
            </files>
        </defaultDocument>
    </system.webServer>
</configuration>
```

1. **Configure Permissions**
2. **Set Folder Permissions**:
    - Ensure that the IIS user (typically **IUSR** and **IIS_IUSRS**) has the necessary permissions to read from the Laravel application directory and write to storage, bootstrap, and cache directories within your Laravel application.

### **Install phpredis Extension**

1. **Download phpredis**:
    - Visit the [phpredis GitHub releases page](https://github.com/phpredis/phpredis/releases) to download the DLL file compatible with your PHP version and thread safety (nts vs ts).
    - Make sure to match the PHP version and architecture (x64).
2. **Install the phpredis DLL**:
    - Extract the downloaded ZIP file and copy the **php_redis.dll** file to your PHP extensions directory (typically **C:\\php\\ext**).
3. **Configure PHP to Use phpredis**:
    - Open your **php.ini** file located in your PHP installation directory.
    - Add the following line to dynamically load the phpredis extension:

```extension=php_redis.dll```

- Save changes and close the file.

1. **Restart Services**

- Restart your web server

**PostgreSQL installation**

Download the [PostgreSQL](https://www.enterprisedb.com/downloads/postgres-postgresql-downloads) setup file and follow the on-screen instructions to install including the PostGIS extension
