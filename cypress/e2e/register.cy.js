import { faker } from '@faker-js/faker';

describe('user can register', () => {
  beforeEach(() => {
    cy.visit('http://127.0.0.1:8080')
    cy.get('a').contains('Create new account').click();
  })
  it('passes', () => {
    const password = faker.internet.password();
    cy.get('input[name="mail"]').type(faker.internet.email());
    cy.get('input[name="name"]').should('not.exist');
    cy.get('input[name="pass[pass1]"]').type(password);
    cy.get('input[name="pass[pass2]"]').type(password);
    cy.get('input[type="submit"]').contains('Create new account').click();
    cy.contains('Registration successful. You are now logged in.');
  })
  it('with special characters', () => {
    const password = faker.internet.password();
    cy.get('input[name="mail"]').type(faker.internet.email(null, null, null, {
      allowSpecialCharacters: true,
    }).replace('=', ''));
    cy.get('input[name="name"]').should('not.exist');
    cy.get('input[name="pass[pass1]"]').type(password);
    cy.get('input[name="pass[pass2]"]').type(password);
    cy.get('input[type="submit"]').contains('Create new account').click();
    cy.contains('Registration successful. You are now logged in.', {timeout: 1000});
  })
  it('with invalid email', () => {
    const password = faker.internet.password();
    cy.get('input[name="mail"]').type('invalid');
    cy.get('input[name="name"]').should('not.exist');
    cy.get('input[name="pass[pass1]"]').type(password);
    cy.get('input[name="pass[pass2]"]').type(password);
    cy.get('input[type="submit"]').contains('Create new account').click();
    cy.get('input[name="mail"]').then($input => {
      expect($input[0].validationMessage).to.eq("Please include an '@' in the email address. 'invalid' is missing an '@'.")
    })
  })
})
