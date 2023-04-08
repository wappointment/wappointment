describe('My First Test', () => {
    it('clicking "type" navigates to a new url', () => {
      cy.visit('/booking-page/')
  
      cy.get('.wbtn.wbtn-booking.wbtn-primary').click()
  
      // Should be on a new URL which includes '/commands/actions'
      cy.contains('Consultation')
    })
  })
  