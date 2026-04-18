import { inject, Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { firstValueFrom, switchMap } from 'rxjs';
import { User } from '../../store/auth/auth.store';

interface LoginDTO { email: string; password: string; }
interface RegisterDTO { name: string; email: string; password: string; }
interface AuthResponse { user: User; }

@Injectable({ providedIn: 'root' })
export class AuthService {
  private http = inject(HttpClient);
  private api = '/api';

  async login(credentials: LoginDTO): Promise<User> {
    // CSRF handshake obrigatório antes de mutações Sanctum
    const res = await firstValueFrom(
      this.http.get('/sanctum/csrf-cookie').pipe(
        switchMap(() =>
          this.http.post<AuthResponse>(`${this.api}/auth/login`, credentials),
        ),
      ),
    );
    return res.user;
  }

  async register(data: RegisterDTO): Promise<User> {
    const res = await firstValueFrom(
      this.http.get('/sanctum/csrf-cookie').pipe(
        switchMap(() =>
          this.http.post<AuthResponse>(`${this.api}/auth/register`, data),
        ),
      ),
    );
    return res.user;
  }

  async logout(): Promise<void> {
    await firstValueFrom(this.http.post(`${this.api}/auth/logout`, {}));
  }

  async me(): Promise<User> {
    return firstValueFrom(this.http.get<User>(`${this.api}/auth/me`));
  }

  async forgotPassword(email: string): Promise<void> {
    await firstValueFrom(
      this.http.post(`${this.api}/auth/forgot-password`, { email }),
    );
  }

  async resetPassword(token: string, email: string, password: string): Promise<void> {
    await firstValueFrom(
      this.http.post(`${this.api}/auth/reset-password`, { token, email, password }),
    );
  }
}
