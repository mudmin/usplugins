<?php
//this is in a .php file to prevent cacheing during dev
?>
<style media="screen">
a, a:hover{
  text-decoration:none;
  cursor: pointer;
  color: black;
}

body {
  box-sizing: border-box;
  /* background-color: #2299ff; */

}
.container {
  padding: 20px;
  margin: 20px auto;
  text-align: center;
  max-width: 99%;
}

.module {
  display: inline-block;
  margin-right: 3%;
  margin-bottom: 5%;

  width: 100%;
  min-width: 220px;
  vertical-align: text-top;

  &:last-child {
    margin-right: 0;
  }

  & > h2 { color: black; }
}

h3{
  color:black;
}

table{
  background-color:white;
}
.card {
  height:15em !important;
  padding:1em;
  /* background-color: #2299ff; */
  /* background-color: #ffffff; */
  text-align: left;
  border-radius: 4px;
  border:2px solid black;
  overflow: hidden;
}

.card-footer {
  background-color: rgba(0,0,0,0);
  margin-bottom: -1em;
  border:none;
  display: flex;
  flex-direction: column;
  margin-top: auto;
  align-self: flex-end;
}

.card__supporting-text {
  font-size:1.05em;

}

</style>
