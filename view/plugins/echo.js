/* if (process.client) {
  window.io = require('socket.io-client')
}

export default function ({
  $auth,
  $echo,
}) {
  $echo.options = {
    broadcaster: 'socket.io',
    host: 'http://localhost:6001',
    authEndpoint: process.env.baseUrl + 'broadcasting/auth',
    client: io,
    authModule: true,
    auth: {
      headers: {
        'Authorization': $auth.$storage._state['_token.local']
      }
    }
  }
  $echo.connector.options = {
    broadcaster: 'socket.io',
    host: 'http://localhost:6001',
    //host: process.env.socketUrl,
    authEndpoint: process.env.baseUrl + 'broadcasting/auth',
    authModule: true,
    auth: {
      headers: {
        'Authorization': $auth.$storage._state['_token.local']
      }
    }
  }
}
 */
