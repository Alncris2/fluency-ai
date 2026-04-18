import { Injectable, OnDestroy } from '@angular/core'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { environment } from '@environment/environment'

declare global {
  interface Window {
    Pusher: typeof Pusher
  }
}

@Injectable({ providedIn: 'root' })
export class EchoService implements OnDestroy {
  private echo: Echo<'reverb'> | null = null

  connect(token: string): void {
    if (this.echo) return

    window.Pusher = Pusher

    this.echo = new Echo({
      broadcaster: 'reverb',
      key: environment.reverb.appKey,
      wsHost: environment.reverb.host,
      wsPort: environment.reverb.port,
      wssPort: environment.reverb.port,
      forceTLS: environment.reverb.scheme === 'https',
      enabledTransports: ['ws', 'wss'],
      authEndpoint: '/broadcasting/auth',
      auth: {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    })
  }

  disconnect(): void {
    this.echo?.disconnect()
    this.echo = null
  }

  channel(name: string) {
    return this.echo?.channel(name)
  }

  privateChannel(name: string) {
    return this.echo?.private(name)
  }

  ngOnDestroy(): void {
    this.disconnect()
  }
}

