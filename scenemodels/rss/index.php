<?php
require_once '../autoload.php';

$modelDaoRO = \dao\DAOFactory::getInstance()->getModelDaoRO();
require '../view/header.php';
?>
  <h1>RSS Feeds</h1>
  <table>
    <tr><th>Keeping Up To Date</th></tr>
    <tr>
      <td>
        <p>To ensure you don't miss anything the following RSS feeds are available:</p>
        <ul>
          <li><a href="news.php">News</a> FGFSDB site news</li>
          <li><a href="models.php">Models</a> Recent model additions</li>
        </ul>
        <br/><br/>
      </td>
    </tr>
  </table>
<?php require '../view/footer.php'; ?>
