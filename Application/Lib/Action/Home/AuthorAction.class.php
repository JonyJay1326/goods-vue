<?php

/**
 * User: yangsu
 * Date: 17/7/31
 * Time: 11:10
 */
class AuthorAction extends Action
{
    public $base_arr = [];
    public $base_del_arr = [];

    /**
     * 同步节点
     */
    public function sync_node()
    {
//        list($files, $customer_functions) = $this->get_model_data();
        $Node = M('node', 'bbm_');
        $node = $Node->select();
        print_r($node);

    }

    private function sync_one_node()
    {

    }

    private function sync_two_node()
    {

    }

    private function sync_three_node()
    {

    }

    /**
     * 获取所有模型
     */
    public function get_all_model()
    {
        list($files, $customer_functions) = $this->get_model_data();
        $this->assign('customer_functions', $customer_functions);
        $this->assign('files', $files);
        $this->display();
    }

    protected function getController()
    {
        $module_path = __DIR__;  //控制器路径
        if (!is_dir($module_path)) return null;
        $module_path .= '/*.class.php';
        $ary_files = glob($module_path);
        foreach ($ary_files as $file) {
            if (is_dir($file)) {
                continue;
            } else {
                $files[] = basename($file, C('DEFAULT_C_LAYER') . '.class.php');
            }
        }
        return $files;
    }

    /**
     * @param $model
     * @return array
     */
    private function get_action($model)
    {
        $functions = get_class_methods(get_class(A($model)));
        $inherents_functions = array('_initialize', '__construct', 'getActionName', 'isAjax', 'display', 'show', 'fetch', 'buildHtml', 'assign', '__set', 'get', '__get', '__isset', '__call', 'error', 'success', 'ajaxReturn', 'redirect', '__destruct', '_empty', 'test', '_get_menu', '_get_menu_all', 'request_do', 'setParams', 'generateMethod', 'getDataDirectory', 'backCommonLanguagePackage', 'getCommonFileContent', 'import_supplier_customer', 'import_translation', 'import_supplier_customer','show_log','clean','getParams');
        $base_del = array('_initialize', '_get_menu', '_get_menu_all', '__get', '__construct', '__set', '__isset', '__call', '__destruct', 'get');
        $base_del_arr = $this->base_del_arr;
        $inherents_functions_b = array_merge($inherents_functions, $base_del_arr);
        foreach ($functions as $func) {
            $reflection = new ReflectionMethod (A($model), $func);
            if ($model == 'Base') {
                if (!in_array(trim($func), $base_del)) {
                    $funcs['action'] = $func;
                    $funcs['doc'] = $reflection->getDocComment();
                    if ($reflection->isPublic()) {
                        $customer_functions[] = $funcs;
                    }
                }
            } else {
                if (!in_array(trim($func), $inherents_functions_b)) {
                    $funcs['action'] = $func;
                    $funcs['doc'] = $reflection->getDocComment();
                    if ($reflection->isPublic()) {
                        $customer_functions[] = $funcs;
                    }
                }
            }
        }
        return $customer_functions;
    }

    private function get_base()
    {
        $this->base_arr = $this->get_action('Base');
        return $this->base_arr;
    }

    /**
     * @return array
     */
    private function get_model_data()
    {
        $files = $this->getController();
        foreach ($files as $m) {
            $customer_functions[$m] = $this->get_action($m);
        }
        $customer_functions['base'] = $this->get_base();
        return array($files, $customer_functions);
    }

}