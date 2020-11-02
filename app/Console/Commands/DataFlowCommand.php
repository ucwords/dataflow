<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\DataFlow\Exception\ParamsErrorException;
use App\DataFlow\Exception\ConfigErrorException;

use Carbon\Carbon;

class DataFlowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:flow {job} {date?} {argv?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'data flow 核心调度';

    /** @var int 当前日期 */
    protected $thisDate;

    /** @var array 传入参数 */
    protected $argv;

    /** @var string 当前执行任务 */
    protected $job;

    /** @var bool 是否静默输出 */
    protected $quite = false;

    /** @var array 允许的写入类型 */
    protected $writeType = [

        'insert',           // 等效 INSERT INTO
        'insertOrUpdate',   // 等效 INSERT INTO ... ON DUPLICATE KEY UPDATE ...
        'insertOrIgnore',   // 等效 IGNORE INSERT IGNORE INTO

        'delete',           // 等效 DELETE WHERE id = ?
        'deleteByWhere',    // 等效 DELETE WHERE Column_1 = ? AND Column_2 = ?

        'update',           // 等效 UPDATE WHERE id = ?
        'updateByWhere',    // 等效 UPDATE WHERE Column_1 = ? AND Column_2 = ?

    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            // 运行环境初始化
            $this->initRunEnv();

            // 运行参数初始化
            $this->initRunParams();

            // 读取配置文件
            $config = $this->loadConfig();

            // 任务组递归调用
            if (preg_match('/.*_job_group$/', $this->job)) {

                $this->runJobGroup($config);

                return;
            }

            // 传参动态更改文件配置
            $config = $this->dynamicReplaceConfig($config);

            // 补全可缺省配置项
            $config = $this->completeDefaultConfig($config);

            // 检查配置有效性 此配置在解析时候进行判断

            // 数据源读取
            $originalData = $this->getOriginalData($config);


        } catch (ConfigErrorException $ce) {

            $this->error($ce->getErrorMsg());

        } catch (ParamsErrorException $pe) {

            $this->error($pe->getErrorMsg());

        }  catch (\Exception $e) {

            $this->error($e->getMessage());
        }
    }


    protected function getDataSource($config)
    {
        // 解析数据源类型
        switch ($config['source_driver']) {

            case 'mysql' :
                //
                break;

            case 'api' :
                //
                break;

            case 'es' :

                break;

            default:

                throw new ConfigErrorException("未定义的数据源驱动类型: {$config['source_driver']}");
        }
    }

    /**
     * 补全目标字段可缺省项
     *
     * @param $config
     * @return mixed
     */
    protected function completeDefaultConfig($config)
    {
        foreach ($config['target_fields'] as $field => &$item) {

            // fields 为字符串 这种情况是最简写的配置 故需要补全所有后续判断所需的 key
            // 补全 source_field、alias、calc、callback
            if (!is_array($item)) {
                $config['target_fields'][$field] =  [
                    'source_field' => $field,
                    'alias'        => null,
                    'calc'         => null,
                    'callback'     => null,
                    'ignore'       => null
                ];
            }
        }

        return $config;
    }

    /**
     * 动态传参
     *
     * @param $config
     * @return mixed
     * @throws ConfigErrorException
     */
    protected function dynamicReplaceConfig($config)
    {
        // 调试模式
        if (!empty($this->argv['debug'])) $config['debug'] = true;

        // 写入类型
        if (!empty($this->argv['write_type'])) {

            if (!isset($this->writeType[$this->argv['write_type']])) {

                $msg = "动态传入的写入类型 {$this->argv['write_type']} 未定义！";
                $this->standardOutInfo($msg, 'error');

                throw new ConfigErrorException($msg);
            }

            return $config['write_type'] = $this->argv['write_type'];
        }

        // 清除设定
        if (!empty($this->argv['clean'])) unset($config['no_clean']);
        if (!empty($this->argv['no_clean'])) $config['no_clean'] = true;

    }


    /**
     * 任务获取
     *
     * @return \Illuminate\Config\Repository|mixed
     * @throws ConfigErrorException
     */
    private function loadConfig()
    {

        $config = config("dataflow.{$this->job}", []);

        if (empty($config)) {

            $msg = "未能设别任务: {$this->job}, 检查是否正常配置";

            $this->standardOutInfo($msg, 'error');

            throw new ConfigErrorException($msg);
        }

        return $config;
    }

    /**
     * 运行前参数初始化
     */
    private function initRunParams()
    {
        // 日期处理
        $this->thisDate = $this->argument('date') ?: date('Ymd', time());

        // 其他参数
        $argv = $this->argument('argv') ?: [];
        $this->argv = [];

        if (is_scalar($argv)) {

            parse_str($argv ?? '', $params);
            $this->argv = $params;
        }

        // 当前任务
        $this->job = $this->argument('job');

        $this->standardOutInfo('运行前参数初始化完成');

    }

    /**
     * 运行环境初始化
     */
    private function initRunEnv()
    {
        ini_set('memory_limit', env('INIT_MEMORY_LIMIT', '5120M'));
        ini_set('display_errors', env('INIT_DISPLAY_ERRORS', 'on'));

        error_reporting(E_ALL);
        set_time_limit(0);

        $this->standardOutInfo('运行环境初始化完成');
    }

    /**
     * 标准输出
     *
     * @param $msg
     * @param string $level
     * @param bool $timePrefix
     */
    private function standardOutInfo($msg, $level = 'info', $timePrefix = true)
    {
        if (!$this->quite) {

            if ($timePrefix) {
                $msg = date('Y-m-d H:i:s') . ' [' . $level . '] ' . $msg;
            }

            $this->info($msg);
        }
    }
}
