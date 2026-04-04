import { Component } from '@angular/core'
import { ChatContactComponent } from './components/chat-contact/chat-contact.component'
import { MessageComponent } from './components/message/message.component'
import type { ContactType } from './data'

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [ChatContactComponent, MessageComponent],
  templateUrl: './chat.component.html',
  styles: ``,
})
export class ChatComponent {
  profileDetail!: ContactType

  receiveDataFromChild(data: ContactType) {
    this.profileDetail = data
  }
}
