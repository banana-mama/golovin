<?php

use GraphAware\Bolt\Record\RecordView;
use classes\DB\DBredis;


/**
 * @var array[]      $workersList
 * @var RecordView[] $workersRelations
 * @var string[]     $descriptions
 * @var DBredis      $redis
 */
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Сотрудники</title>
  <?php require('./styles.php') ?>
</head>
<body>
<section>

    <section>
        <h2>Мои сотрудники (mongo)</h2>

      <?php $maxID = 0; ?>
      <?php foreach ($workersList as $worker): ?>
          <table>
              <tr>
                  <th>Название</th>
                  <th>Значение</th>
                  <th colspan="2">Действие</th>
              </tr>
            <?php foreach ($worker as $key => $value): ?>
              <?php if (($key === 'id') && ($value > $maxID)) $maxID = $value; ?>
                <form method="POST">
                    <tr>
                        <th>
                            <input type="text" name="new_key" value="<?= $key ?>">
                          <?php if ($redis->isExists('desc__' . $key)): ?>
                              <p title="<?= $redis->read('desc__' . $key) ?>">читать описание...</p>
                          <?php endif; ?>
                        </th>
                        <td>
                            <input type="text" name="value" value="<?= $value ?>">
                          <?php if ($redis->isExists('val__' . $value)): ?>
                              <p title="<?= $redis->read('val__' . $value) ?>">читать описание...</p>
                          <?php endif; ?>
                        </td>
                        <td>
                            <input type="hidden" name="old_key" value="<?= $key ?>">
                            <input type="hidden" name="id" value="<?= $worker['id'] ?>">
                            <select name="type">
                                <option value="mongo__update-field">Обновить</option>
                                <option value="mongo__delete-field">Удалить</option>
                            </select>
                        </td>
                        <td>
                            <button>Выполнить</button>
                        </td>
                    </tr>
                </form>
            <?php endforeach; ?>
              <form method="POST">
                  <tr>
                      <th><input type="text" name="new_key"></th>
                      <td><input type="text" name="value"></td>
                      <td colspan="2" style="text-align: center;">
                          <input type="hidden" name="type" value="mongo__create-field">
                          <input type="hidden" name="id" value="<?= $worker['id'] ?>">
                          <button>Добавить поле</button>
                      </td>
                  </tr>
              </form>
              <form method="POST">
                  <tr>
                      <td colspan="4" style="text-align: center;">
                          <input type="hidden" name="type" value="mongo__delete">
                          <input type="hidden" name="id" value="<?= $worker['id'] ?>">
                          <button>Удалить сотрудника</button>
                      </td>
                  </tr>
              </form>
          </table>
      <?php endforeach; ?>

        <br>
        <form method="POST">
            <input type="hidden" name="type" value="mongo__create">
            <input type="hidden" name="id" value="<?= ($maxID + 1) ?>">
            <button>Добавить сотрудника</button>
        </form>

    </section>

    <section>

        <h2>Отношения (neo4j)</h2>

      <?php foreach ($workersRelations as $workersRelation): ?>
          <form method="POST">
              <div>
                  <span><?= $workersRelation->values()[0]->name ?></span>
                  <span> --- <?= array_search($workersRelation->values()[1]->type(), $relations) ?> --▻ </span>
                  <span><?= $workersRelation->values()[2]->name ?></span>
                  <input type="hidden" name="from-id" value="<?= $workersRelation->values()[0]->id ?>">
                  <input type="hidden" name="to-id" value="<?= $workersRelation->values()[2]->id ?>">
                  <input type="hidden" name="relation-type" value="<?= $workersRelation->values()[1]->type() ?>">
                  <input type="hidden" name="type" value="neo4j__delete">
                  <span><button>Удалить связь</button></span>
              </div>
          </form>
          <br>
      <?php endforeach; ?>

        <div>
            <form method="POST">
                <select name="workerFrom">
                  <?php foreach ($workersList as $worker): ?>
                      <option value="<?= $worker['id'] ?>|<?= $worker['name'] ?? '' ?>"><?= ($worker['name'] ?? $worker['id']) ?></option>
                  <?php endforeach; ?>
                </select>
                <select name="relation">
                  <?php foreach ($relations as $name => $relation): ?>
                      <option value="<?= $relation ?>"><?= $name ?></option>
                  <?php endforeach; ?>
                </select>
                <select name="workerTo">
                  <?php foreach ($workersList as $worker): ?>
                      <option value="<?= $worker['id'] ?>|<?= $worker['name'] ?? '' ?>"><?= ($worker['name'] ?? $worker['id']) ?></option>
                  <?php endforeach; ?>
                </select>
                <input type="hidden" name="type" value="neo4j__create">
                <button>Добавить связь</button>
            </form>
        </div>

    </section>

    <section>

        <h2>Описания (MySQL)</h2>

        <table>
            <tr>
                <th>Ключ</th>
                <th>Описание</th>
                <th>Действие</th>
                <td></td>
            </tr>
          <?php foreach ($handbook as $id => $data): ?>
              <form method="POST">
                  <tr>
                      <td style="text-align: center;"><?= $data['key'] ?></td>
                      <td><textarea name="value"><?= $data['description'] ?></textarea></td>
                      <td>
                          <input type="hidden" name="key" value="<?= $data['key'] ?>">
                          <select name="type">
                              <option value="mysql__update">Обновить</option>
                              <option value="mysql__delete">Удалить</option>
                          </select>
                      </td>
                      <td>
                          <button>Выполнить</button>
                      </td>
                  </tr>
              </form>
          <?php endforeach; ?>

            <form method="POST">
                <tr>
                    <th><input type="text" name="key"></th>
                    <td><textarea name="value"></textarea></td>
                    <td colspan="2" style="text-align: center;">
                        <input type="hidden" name="type" value="mysql__create">
                        <button>Добавить</button>
                    </td>
                </tr>
            </form>

        </table>

    </section>

    <section>

        <h2>Описания (redis)</h2>

        <table>
            <tr>
                <th>Ключ</th>
                <th>Описание</th>
                <th>Действие</th>
                <td></td>
            </tr>
          <?php foreach ($descriptions as $description): ?>
              <form method="POST">
                  <tr>
                      <td style="text-align: center;"><?= $description ?></td>
                      <td><textarea name="value"><?= $redis->read($description) ?></textarea></td>
                      <td>
                          <input type="hidden" name="key" value="<?= $description ?>">
                          <select name="type">
                              <option value="redis__update">Обновить</option>
                              <option value="redis__delete">Удалить</option>
                          </select>
                      </td>
                      <td>
                          <button>Выполнить</button>
                      </td>
                  </tr>
              </form>
          <?php endforeach; ?>

            <form method="POST">
                <tr>
                    <th><input type="text" name="key"></th>
                    <td><textarea name="value"></textarea></td>
                    <td colspan="2" style="text-align: center;">
                        <input type="hidden" name="type" value="redis__create">
                        <button>Добавить</button>
                    </td>
                </tr>
            </form>

        </table>

    </section>

</section>
</body>
</html>
