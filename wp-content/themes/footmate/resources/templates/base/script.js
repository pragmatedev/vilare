class Base {
  constructor(holder) {
    this.holder = holder;
  }
}

document
  .querySelectorAll('[data-template="base"]')
  .forEach(holder => new Base(holder));
