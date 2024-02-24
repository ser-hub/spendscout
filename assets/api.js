const host = "https://localhost:8000";

async function request(method, url, data) {
  const options = {
    method,
    headers: {},
  };

  if (data !== undefined) {
    options.headers["Content-Type"] = "application/json";
    //data.csrf = localStorage.getItem('csrf-token');
    options.body = JSON.stringify(data);
  }

  const response = await fetch(host + url, options);

  try {
    return await response.json().then((response) => {
      /*if (response.csrfToken !== undefined) {
        localStorage.setItem('csrf-token', response.csrfToken);
      }*/

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
