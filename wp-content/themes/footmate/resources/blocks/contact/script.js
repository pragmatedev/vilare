class Contact {
  constructor(holder) {
    this.holder = holder;
  }
}

document
  .querySelectorAll('[data-block="contact"]')
  .forEach(holder => new Contact(holder));
