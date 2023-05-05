import { faker } from '@faker-js/faker';

const authorSut = {
  email: faker.internet.email(),
  password: faker.internet.password(),
}
describe('user can edit their account', function () {
  before(() => {
    cy.registerUser(authorSut.email, authorSut.password)
  })
  it('allows changing email address for account', function () {
    cy.loginAsUser(authorSut.email, authorSut.password)
    // Generate a new email.
    authorSut.email = faker.internet.email();

    cy.get('.block-local-tasks-block').get('a').contains('Edit').click()
    cy.contains('Required if you want to change the Email address or Password below.');
    cy.get('input[name="mail"]').clear().type(authorSut.email);
    cy.get('input[type="submit"]').contains('Save').click();
    cy.contains("Your current password is missing or incorrect; it's required to change the Email.")
    cy.get('input[name="current_pass"]').type(authorSut.password);
    cy.get('input[name="mail"]').clear().type(authorSut.email);
    cy.get('input[type="submit"]').contains('Save').click();
    cy.contains('The changes have been saved.');
    cy.visit('http://127.0.0.1:8080/account/logout')
    cy.loginAsUser(authorSut.email, authorSut.password)
    cy.contains('View profile')
  })
  it('allows changing password', function () {
    cy.loginAsUser(authorSut.email, authorSut.password)
    // Generate a new password.
    const originalPassword = authorSut.password;
    authorSut.password = faker.internet.password();

    cy.get('.block-local-tasks-block').get('a').contains('Edit').click()
    cy.contains('Required if you want to change the Email address or Password below.');
    cy.get('input[name="pass[pass1]"]').type(authorSut.password);
    cy.get('input[name="pass[pass2]"]').type(authorSut.password);
    cy.get('input[type="submit"]').contains('Save').click();
    cy.contains('Your current password is missing or incorrect; it\'s required to change the Password.')
    cy.get('input[name="current_pass"]').type(originalPassword);
    cy.get('input[name="pass[pass1]"]').type(authorSut.password);
    cy.get('input[name="pass[pass2]"]').type(authorSut.password);
    cy.get('input[type="submit"]').contains('Save').click();
    cy.contains('The changes have been saved.');
    cy.visit('http://127.0.0.1:8080/account/logout')
    cy.loginAsUser(authorSut.email, authorSut.password)
  })
})
