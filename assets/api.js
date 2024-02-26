const host = "https://localhost:8000";

async function request(method, url, data) {
  const options = {
    method,
    headers: {},
  };

  if (data !== undefined) {
    options.headers["Content-Type"] = "application/json";
    options.body = JSON.stringify(data);
  }

  // set CSRF Token
  const csrf_token = document.querySelector("meta[name='csrf-token']").getAttribute("content");
  if (csrf_token != undefined) {
    options.headers["anti-csrf-token"] = csrf_token;
  }

  const response = await fetch(host + url, options);

  try {
    return await response.json().then((response) => {

      return response;
    });
  } catch (error) {
    return {
      detail: "Something went wrong"
    }
  }
}

export const get = request.bind(null, "GET");
export const post = request.bind(null, "POST");
export const put = request.bind(null, "PUT");
export const del = request.bind(null, "DELETE");
