<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted ?>
<h3>Here's what you need to know.</h3>
<p>
This plugin essentially allows you to have sub "organizations" inside your UserSpice install. This can be companies, teams, or for selling your software as a service. It allows
some VERY basic user management where the owner and their managers can perform some basic tasks such as password and permission management.
</p>

<h4>Levels</h4>
<p>
You can setup levels that essentially determine 2 things. They determine how many users the org can have and which UserSpice permission levels they are allowed to assign.
</p>

<h4>Roles</h4>
<p>
There are two "built in" roles to the SAAS plugin. Owner and Manager.  Owner is the person who has overall control of the org.  The owner can also delegate authority to managers.
Both Managers and owners have tables on their account.php page that allows them to manage their users.
</p>

<h4>Permissions</h4>
<p>
This plugin cannot manage permission levels 1(user) or 2(admin).  However, you can create additional permission levels on your base UserSpice install. Any permission level above 2 can become
"assignable" by various organizations.  Let's say you're running a point of sale software, you can add Managers, Cashiers, and Supervisors and then allow your orgs to determine who is who.
It's still up to you to code your UserSpice pages as you always have and setup permissions such as "only supervisors can visit this page", however this plugin allows the organization to
determine who is a supervisor.
</p>
<p>Important: Just because you take a permission away from a level, does not mean that that permission is automatically "stripped" from all the users of those orgs. You will have to do
that manually. </p>

<h4>Deactivation</h4>
<p>
 When you deactivate a group, all of its users will be deactivated.  If you reactivate, it will be up to them to reactivate their users unless you script that for them. The primary reason
 for this is that they can deactivate their own users, so we don't want to reactivate users by accident.
</p>

<h4>Transferring</h4>
<p>
You can deactivate an org and transfer their users to another org.  Please not that this WILL allow that org to exceed their user limit (since this action is performed by an admin).
</p>

<h4>Reserved</h4>
<p>
Org 1 is "reserved" for the system and this org always has the level that has the most users. You can delete an account and transfer all the users to org 1 if you want a place to store those users.
</p>

<h4>Other Notes</h4>
<p>
Maybe this plugin isn't exactly what you're looking for. Please feel free to contribute more ideas and code or even fork it! You can just copy it from
usersc/plugins/saas to usersc/plugins/yoursaas and edit to your heart's content.
</p>
