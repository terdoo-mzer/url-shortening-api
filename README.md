

# URL Shortening API

Welcome to the URL Shortening API project! This API allows users to shorten URLs, manage their shortened URLs, and view analytics for each shortened URL.

## Getting Started

To get started with the project, follow the instructions below.

### Prerequisites

Make sure you have the following software installed on your machine:

- PHP
- Composer
- CodeIgniter 4
- ngrok (for accessing the project over the public internet)

### Installation

1. Clone the repository to your local machine:

```bash
git clone https://github.com/your-username/url-shortening-api.git
```

2. Navigate to the project directory:

```bash
cd url-shortening-api
```

3. Install dependencies:

```bash
composer install
```

4. Run the project:

```bash
php spark serve
```

The project will run on port 8080 by default.

### Registration and Login

Before accessing the protected routes, users need to register and log in to get a JWT token.

1. Register:
   - Send a POST request to `auth/v1/register` endpoint with the following payload:
     ```json
     {
       "first_name": "YourFirstName",
       "last_name": "YourLastName",
       "email": "your@email.com",
       "password": "YourPassword"
     }
     ```

2. Login:
   - Send a POST request to `auth/v1/login` endpoint with the following payload:
     ```json
     {
       "email": "your@email.com",
       "password": "YourPassword"
     }
     ```

   - Upon successful login, you will receive a JWT token in the response.

### Accessing Protected Routes

To access protected routes, include the JWT token in the `Authorization` header of your requests.

1. Create Shortened URL:
   - Send a POST request to `/api/v1/shorten_url` endpoint with the URL you want to shorten.

2. Revoke URL:
   - Send a PUT request to `/api/v1/revoke_url/{short_code}` endpoint with the short code of the URL you want to revoke.

3. Get All URLs:
   - Send a GET request to `/api/v1/get_all_urls/{user_id}` endpoint to retrieve all shortened URLs.

4. Get URL Details:
   - Send a GET request to `/api/v1/get_single_url_details/{url_id}` endpoint to retrieve details of a specific shortened URL.

### Setting Up ngrok

To access the project over the public internet using ngrok, follow these steps:

1. Download ngrok from the official website: [ngrok.com](https://ngrok.com/download).

2. Extract the ngrok executable to a directory of your choice.

3. Start ngrok tunneling by running the following command in your terminal:

```bash
./ngrok http 8080
```

4. ngrok will generate a public URL (e.g., `https://5cd1-******-34-80.ngrok-free.app`). Use this URL to access your project from anywhere on the internet.

### NOTE!!!
Please  note that you have to set up ngrok. Running the project on local will not yield as expected. The reason is because when you generate a short url and run it in the browser, The backend expects to get your IP address and forwards it thereafter to a thirdparty API service to get analytics. This data is logged in the database. The aim is to provide real time tracking of url use.

### Credit:
I used the Geolocation API for tracking IP addresses [Geolocation API](https://ip-api.com/docs/api:json)



