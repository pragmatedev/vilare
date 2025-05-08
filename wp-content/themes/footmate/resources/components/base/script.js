class Base {
  constructor(holder) {
    this.holder = holder;
  }
}

document
  .querySelectorAll('[data-component="base"]')
  .forEach(holder => new Base(holder));
