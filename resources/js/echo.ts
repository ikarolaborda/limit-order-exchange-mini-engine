import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import axios from 'axios'

type EchoInstance = InstanceType<typeof Echo>

declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: EchoInstance
  }
}

window.Pusher = Pusher

interface AuthorizerCallback {
  (error: boolean, data?: unknown): void
}

interface Authorizer {
  authorize: (socketId: string, callback: AuthorizerCallback) => void
}

export function createEcho(token: string): EchoInstance | null {
  const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY
  const pusherHost = import.meta.env.VITE_PUSHER_HOST
  const pusherPort = import.meta.env.VITE_PUSHER_PORT

  if (!pusherKey || !pusherHost) {
    console.warn('Pusher not configured, real-time updates disabled')
    return null as unknown as EchoInstance
  }

  return new Echo({
    broadcaster: 'pusher',
    key: pusherKey,
    wsHost: pusherHost,
    wsPort: pusherPort ? parseInt(pusherPort, 10) : 6001,
    wssPort: pusherPort ? parseInt(pusherPort, 10) : 6001,
    forceTLS: import.meta.env.VITE_PUSHER_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    cluster: 'mt1',
    authorizer: (channel: { name: string }): Authorizer => ({
      authorize: (socketId: string, callback: AuthorizerCallback): void => {
        axios
          .post(
            '/api/broadcasting/auth',
            {
              socket_id: socketId,
              channel_name: channel.name,
            },
            {
              headers: {
                Authorization: `Bearer ${token}`,
              },
            },
          )
          .then((response) => {
            callback(false, response.data)
          })
          .catch((error) => {
            callback(true, error)
          })
      },
    }),
  })
}

