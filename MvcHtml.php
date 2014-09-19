<?php
/**
 * Author:    Adam Jaso <mkltwain@gmail.com>
 *
 */ 


class MvcHtml {
    public static function select(array $value) {
        $name = $value['name'];
        $options = isset($value['options']) ? $value['options'] : array();
        $selected = isset($value['selected']) ? $value['selected'] : -1;
        $attrs = isset($value['attrs']) ? $value['attrs'] : '';
        ob_start();
        ?>
    <select id="<?php echo self::mkId($name); ?>" name="<?php echo self::mkName($name); ?>" <?php echo $attrs; ?>>
        <?php
        foreach ($options as $value => $name) {
            ?>
        <option value="<?php echo $value; ?>" <?php echo $value == $selected ? 'selected=""' : ''; ?>><?php echo $name; ?></option>
            <?php
        }
        ?>
    </select>
        <?php
        return ob_get_clean();
    }

    public static function input(array $value) {
        $name = $value['name'];
        $type = isset($value['type']) ? $value['type'] : 'text';
        $attrs = isset($value['attrs']) ? $value['attrs'] : '';
        $value = isset($value['value']) ? $value['value'] : '';
        ob_start();
        ?>
    <input id="<?php echo self::mkId($name); ?>" type="<?php echo $type; ?>" name="<?php echo self::mkName($name); ?>" value="<?php echo $value; ?>" <?php echo $attrs; ?>>
        <?php
        return ob_get_clean();
    }

    public static function textarea(array $value) {
        $name = $value['name'];
        $attrs = isset($value['attrs']) ? $value['attrs'] : '';
        $value = isset($value['value']) ? $value['value'] : '';
        ob_start();
        ?>
        <textarea id="<?php echo self::mkId($name); ?>" name="<?php echo self::mkName($name); ?>" <?php echo $attrs; ?>><?php echo $value; ?></textarea>
        <?php
        return ob_get_clean();
    }

    private static function mkId($id) {
        return Mvc::$appPrefix . '-' . $id;//preg_replace('/[\W]+/i', '', $id);
    }

    public static function mkName($name) {
        return preg_replace('/\s+/i', '_', $name);//preg_replace('/[^a-zA-Z0-9\[\]\-_]+/i', '', $name);
    }

    public static function formField($name, $value, $afterText = '') {
        $value['name'] = isset($value['name']) ? $value['name'] : $name;
        $input = isset($value['input']) ? $value['input'] : (isset($value['options']) ? 'select' : 'input');
        ob_start();
        ?>
    <label for="<?php echo self::mkId($value['name']); ?>"><?php echo $name . $afterText ?></label>
        <?php
        echo call_user_func(array('self', $input), $value);
        return ob_get_clean();
    }

    public static function createPages($totalCount, $currPage, $perPage, $baseUrl) {
        ob_start();
        $numPages = ((int) ($totalCount / $perPage)) + 1;
        echo ' | ';
        for ($i = 1; $i <= $numPages; $i++) {
            if ($i == $currPage) {
                echo $i;
            } else {
                ?><a href="<?php echo Mvc::paramReplace('&pg', $i, $baseUrl); ?>"><?php echo $i; ?></a><?php
            }
            echo ' | ';
        }
        return ob_get_clean();
    }
} 