const fetchData = async (params = {}) => {
    const { endpoint, method = "GET", headers = {}, body = {} } = params;
    const fullUrl = `${_base_url_}${endpoint}`;
    
    const options = {
        method,
        headers,
    };

    if (method !== "GET") {
        options.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(fullUrl, options);

        if (!response.ok) {
            return [{ error: "Error fetching data" }, null];
        }

        const data = await response.json();
        return [null, data];
    } catch (err) {
        return [err.message, null];
    }
}

