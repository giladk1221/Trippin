### **Query Parameters**
| Parameter   | Type    | Required | Description                                   |
|-------------|---------|----------|-----------------------------------------------|
| `username`  | string  | Yes      | The username of the user to fetch expenses for. |
| `month`     | integer | No       | The month to filter expenses (1–12).         |
| `year`      | integer | No       | The year to filter expenses (e.g., 2024).    |
| `api_key`   | string  | Yes      | A valid API key to authenticate the request. |

### **Headers**
| Header         | Value                |
|----------------|----------------------|
| `Content-Type` | `application/json`  |

---

## Example Requests

### **1. Fetch All Expenses for a User**

GET http://benzd.mtacloud.co.il/Trippin/backend/get_expenses.php?username=amitm&api_key=your-api-key

### **2. Fetch All Expenses for a User by Month and Year

GET http://benzd.mtacloud.co.il/Trippin/backend/get_expenses.php?username=amitm&month=01&year=2025&api_key=your-api-key



---
### Example Response

```json
{
    "status": "success",
    "data": [
        {
            "reason": "Hotel",
            "amount": 500,
            "currency": "USD",
            "date": "2024-12-19",
            "full_name": "John Doe"
        },
        {
            "reason": "Flight",
            "amount": 200,
            "currency": "USD",
            "date": "2024-12-10",
            "full_name": "John Doe"
        }
    ]
}


## Error Codes

The API returns the following error codes and corresponding messages when a request fails:

| **HTTP Code** | **Error Message**       | **Description**                                                                 |
|---------------|-------------------------|---------------------------------------------------------------------------------|
| `400`         | `Missing username`      | The `username` parameter is required but was not provided in the request.      |             |
| `401`         | `Missing API key`       | The `api_key` parameter is required but was not provided in the request.       |
| `401`         | `Invalid API key`       | The provided API key is invalid or not active.                                 |
| `404`         | `No data found`         | No expenses were found matching the given filters.                             |
| `500`         | `An error occurred`     | A server-side error occurred while processing the request.                     |

---

### Example Error Responses

#### **Missing Username**
```json
{
    "error": "Missing username"
}

#### **Missing API key**
```json
{
    "error": "Missing API key"
}

#### **Invalid API key**
```json
{
    "error": "Invalid API key"
}



