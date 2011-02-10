<h1>Create a new page for your child</h1>

<div class="clearfix">
  <?php getTemplate()->display('partials/paragraphRight.php'); ?>

  <p>
    <form method="post" action="/child/new" id="childNewForm">
      <div data-role="fieldcontain">
        <label for="childName">Child's name</label>
        <input type="text" name="childName" required="required" tabindex="1">
      </div>

      <div data-role="fieldcontain">
        <label for="childBirthDate">Birthdate &amp; time<em>(this format 6/19/2010 10:06 am)</em></label>
        <input type="text" name="childBirthDate" date="mm/dd/yyyy" tabindex="2">
      </div>

      <div data-role="fieldcontain">
        <label for="childDomain">Web page URL<em>(http://johnny.meltsmyheart.com - just enter johnny)</em></label>
        <input type="text" name="childDomain" required="required" check-domain="true" check-name="" tabindex="3">
      </div>

      <button class="yellow" type="submit" tabindex="4"><div>Submit</div></button>
    </form>
  </p>
</div>
