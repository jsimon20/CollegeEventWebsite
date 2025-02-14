Download WAMP (Windows) or MAMP (MAC)

cd into your WAMP or MAMP file to get to htdocs ex: /Applications/MAMP/htdocs

Do the following command inside the htdocs folder:
git clone https://github.com/jsimon20/CollegeEventWebsite.git

The repo already has index.php inside so you can delete the default index.php in your htdocs folder


# College Event Website

## Description
A web-based application to manage and view university events. Users can register, create RSOs, and manage events. The application supports different user roles: Super Admin, Admin, and Student.

## Features
- User registration and login
- Create and manage RSOs
- Create and manage events
- View events based on visibility (public, private, RSO)
- Comment and rate events
- Social media integration

## Installation
1. Clone the repository.
2. Import the database schema and sample data.
3. Configure the database connection in `includes/db_connect.php`.
4. Start the web server and navigate to the application.

## Usage
- Register as a user.
- Login to access the application features.
- Admins can create and manage RSOs and events.
- Students can view events, comment, and rate them.

## License
This project is licensed under the MIT License.