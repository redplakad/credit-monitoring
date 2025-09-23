import axios from 'axios';


function getCookie(name: string) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop()?.split(';').shift() || '';
  return '';
}

function getCsrfToken() {
  // Ambil dari cookie XSRF-TOKEN (untuk SPA/axios)
  return decodeURIComponent(getCookie('XSRF-TOKEN'));
}

  return axios.post('/login', data, {
    withCredentials: true,
    headers: {
      'X-XSRF-TOKEN': getCsrfToken(),
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
}

  return axios.post('/logout', {}, {
    withCredentials: true,
    headers: {
      'X-XSRF-TOKEN': getCsrfToken(),
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
}

  return axios.post('/register', data, {
    withCredentials: true,
    headers: {
      'X-XSRF-TOKEN': getCsrfToken(),
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
}
