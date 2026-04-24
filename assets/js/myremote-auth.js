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
    const isFormData = options && options.body instanceof FormData;
    const headers = {
      ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
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

  function storedUser() {
    try {
      return JSON.parse(localStorage.getItem(USER_KEY) || 'null');
    } catch (error) {
      return null;
    }
  }

  function enhanceHeader() {
    if (!getToken()) return;

    const loginLinks = document.querySelectorAll('a[href="login.html"], a[href="/login"], a[href$="/login"]');
    loginLinks.forEach((link) => {
      link.href = 'mypage.html';
      link.textContent = 'マイページ';
    });

    const registerLinks = document.querySelectorAll('a[href="register.html"], a[href="/register"], a[href$="/register"]');
    registerLinks.forEach((link) => {
      link.href = '#logout';
      link.textContent = 'ログアウト';
      link.addEventListener('click', async (event) => {
        event.preventDefault();
        await window.myremoteAuth.logout();
        window.location.href = 'login.html';
      });
    });
  }

  function rewriteStaticLinks() {
    const labelToHref = {
      'お問い合わせ': 'contact.html',
      '利用規約': 'terms.html',
      'プライバシーポリシー': 'privacy.html',
      'ヘルプセンター': 'faq.html',
    };

    document.querySelectorAll('a[href="#"]').forEach((link) => {
      const label = link.textContent.trim();
      if (labelToHref[label]) {
        link.href = labelToHref[label];
      }
    });
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
    async passwordReset(email) {
      return request('/password-reset', {
        method: 'POST',
        body: JSON.stringify({ email }),
      });
    },
    async contact(payload) {
      return request('/contact', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
    },
    async createApplication(payload) {
      if (payload instanceof FormData) {
        return request('/applications', {
          method: 'POST',
          body: payload,
        });
      }

      return request('/applications', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
    },
    storedUser,
    enhanceHeader,
    rewriteStaticLinks,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      rewriteStaticLinks();
      enhanceHeader();
    });
  } else {
    rewriteStaticLinks();
    enhanceHeader();
  }
})();
