(function () {
  const API_BASE = 'https://enn-musubi.sakura.ne.jp/my-remote/wp-json/myremote/v1';
  const TOKEN_KEY = 'myremoteAuthToken';
  const USER_KEY = 'myremoteUser';

  function getToken() {
    try {
      return localStorage.getItem(TOKEN_KEY) || '';
    } catch (error) {
      return '';
    }
  }

  function setSession(payload) {
    try {
      localStorage.setItem(TOKEN_KEY, payload.token);
      localStorage.setItem(USER_KEY, JSON.stringify(payload.user));
    } catch (error) {}
  }

  function clearSession() {
    try {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
    } catch (error) {}
  }

  async function request(path, options) {
    const token = getToken();
    const headers = {
      'Content-Type': 'application/json',
      ...(options && options.headers ? options.headers : {}),
    };

    if (token) {
      headers.Authorization = 'Bearer ' + token;
    }

    const response = await fetch(API_BASE + path, {
      ...options,
      headers,
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      const message = data.message || '通信に失敗しました。時間をおいて再度お試しください。';
      throw new Error(message);
    }

    return data;
  }

  window.myremoteAuth = {
    getToken,
    setSession,
    clearSession,
    async login(email, password, remember) {
      const data = await request('/login', {
        method: 'POST',
        body: JSON.stringify({ email, password, remember: Boolean(remember) }),
      });
      setSession(data);
      return data;
    },
    async register(payload) {
      const data = await request('/register', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
      setSession(data);
      return data;
    },
    async me() {
      const data = await request('/me', { method: 'GET' });
      try {
        localStorage.setItem(USER_KEY, JSON.stringify(data.user));
      } catch (error) {}
      return data.user;
    },
    async logout() {
      try {
        await request('/logout', { method: 'POST' });
      } finally {
        clearSession();
      }
    },
    async createApplication(payload) {
      return request('/applications', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
    },
  };
})();
