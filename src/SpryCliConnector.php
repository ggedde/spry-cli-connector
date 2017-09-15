<?php

namespace Spry\SpryConnector;

use Spry\Spry;
use Spry\SpryProvider\SpryTools;

// Setup Server Vars for CLI
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

class SpryCliConnector extends SpryTools
{

    private static $cli_path = '';

    private static function find_config()
    {
        $files = [
            getcwd().'/config.php',
            getcwd().'/spry/config.php'
        ];

        foreach($files as $file)
        {
            if(file_exists($file))
            {
                return $file;
            }
        }

        return '';
    }

    public static function run($cli_path='')
    {
        self::$cli_path = $cli_path;

        $args = [];
        $config_file = '';
        $commands = [
            'c' => 'component',
            'clear' => 'clear',
            'h' => 'hash',
            'help' => 'help',
            'i' => 'init',
            'm' => 'migrate',
            't' => 'test',
            'u' => 'up',
            'v' => 'version'
        ];
        $command = '';
        $singletest = '';
        $hash = '';
        $component = '';
        $clear = '';
        $verbose = false;
        $repeat = 1;

        if(!empty($_SERVER['argv']))
        {
            $args = $_SERVER['argv'];
            $key = array_search('--config', $args);
            if($key !== false && isset($args[($key + 1)]))
            {
                $config_file = $args[($key + 1)];
            }

            $key = array_search('--verbose', $args);
            if($key !== false)
            {
                $verbose = true;
            }

            $key = array_search('h', $args);
            if($key === false)
            {
                $key = array_search('hash', $args);
            }
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $hash = $args[($key + 1)];
            }

            $key = array_search('t', $args);
            if($key === false)
            {
                $key = array_search('test', $args);
            }
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $singletest = $args[($key + 1)];
            }

            $key = array_search('c', $args);
            if($key === false)
            {
                $key = array_search('component', $args);
            }
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $component = $args[($key + 1)];
            }

            $key = array_search('clear', $args);
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $clear = $args[($key + 1)];
            }

            $key = array_search('--repeat', $args);
            if($key !== false && isset($args[($key + 1)]))
            {
                $repeat = $args[($key + 1)];
            }

            foreach ($args as $value)
            {
                if(in_array($value, $commands))
                {
                    $command = $value;
                }
                elseif(in_array($value, array_keys($commands)))
                {
                    $command = $commands[$value];
                }
            }
        }

        if(!$command)
        {
            if(array_search('-v', $args) !== false || array_search('--version', $args) !== false)
            {
                $command = 'version';
            }

            if(array_search('-h', $args) !== false || array_search('--help', $args) !== false)
            {
                $command = 'help';
            }
        }

        if(!$command)
        {
            die("Spry -v ".Spry::get_version()."\n\e[91mERROR:\e[0m Spry - Command not Found. For help try 'spry --help'");
        }

        if($command === 'version')
        {
            die("Spry -v ".Spry::get_version());
        }

        if($command === 'help')
        {
            echo "Spry -v ".Spry::get_version()."\n".
            "Usage: spry [command] [value] [--argument] [--argument]... \n\n".
            "List of Commands and arguments:\n\n".
            "\e[1mclear                         \e[0m- Clears specific objects. Currently only supports 'logs'.\n".
            "  ex.     spry clear logs    (clears both API and PHP log files. Does not remove archived logs.)\n\n".
            "\e[1mcomponent | c                 \e[0m- Generate a new Component and add it to your component directory.\n".
            "  ex.     spry component sales_reps    (component classes will follow psr-4 format. ie SalesReps)\n\n".
            "\e[1mhash | h                      \e[0m- Hash a value that procedes it using the salt in the config file.\n".
            "  ex.     spry hash something_to_hash_123\n".
            "  ex.     spry hash \"hash with spaces 123\"\n\n".
            "\e[1mhelp | -h | --help            \e[0m- Display Information about Spry-cli.\n\n".
            "\e[1minit | i                      \e[0m- Initiate a Spry Setup and Configuration with default project.\n\n".
            "\e[1mmigrate | m                   \e[0m- Migrate the Database Schema.\n".
            "  --dryrun                    - Only check for what will be migrated and report back. No actions will be taken.\n".
            "  --destructive               - Delete Fields, Tables and other data that does not match the new Scheme.\n\n".
            "\e[1mtest | t                      \e[0m- Run a Test or all Tests if a Test name is not specified.\n".
            "  --verbose                   - List out full details of the Test(s).\n".
            "  ex.     spry test\n".
            "  ex.     spry test --verbose\n".
            "  ex.     spry test test_123 --verbose\n\n".
            "\e[1mversion | v | -v | --version  \e[0m- Display the Version of the Spry Instalation.\n\n";
        }

        if(!$config_file)
        {
            $config_file = self::find_config();
        }

        if(!$config_file || !file_exists($config_file))
        {
            die("\e[91mERROR:\e[0m No Config File Found. Run SpryCli from the same folder that contains your 'config.php' file or specify the config file with --config");
        }
        Spry::load_config($config_file);
        spl_autoload_register(['Spry\\Spry', 'autoloader']);

        switch($command)
        {
            case 'component':

                $component_sanitized = preg_replace("/\W/", '', str_replace([' ', '-'], '_', $component));
                $component_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $component_sanitized)));

                if(!$component_name)
                {
                    die("\e[91mERROR:\e[0m Missing Component Name.");
                }

                $source_component = self::$cli_path.'/example_project/components/example.php';
                $new_component = Spry::config()->components_dir.'/'.$component_name.'.php';

                if(!is_dir(Spry::config()->components_dir.'/'))
                {
                    die("\e[91mERROR:\e[0m Component Directory is not configured in config.php or not found.");
                }

                if(!is_writable(Spry::config()->components_dir.'/'))
                {
                    die("\e[91mERROR:\e[0m Component Directory Does not seem to be writable.");
                }

                if(file_exists($new_component))
                {
                    die("\e[91mERROR:\e[0m Component with that name already exists.");
                }

                if(!file_exists($source_component))
                {
                    die("\e[91mERROR:\e[0m Missing Source Component Template.");
                }

                if(!copy($source_component, $new_component))
                {
                    die("\e[91mERROR:\e[0m Component could not be created.");
                }

                // Replace Component config_content
                $component_contents = file_get_contents($new_component);
                $component_contents = str_replace('class Example', 'class '.$component_name, $component_contents);
                $component_contents = str_replace('examples_table', strtolower($component_sanitized), $component_contents);
                file_put_contents($new_component, $component_contents);

                echo "\n\e[92mComponent Created Successfully!\e[0m\n".
                $new_component."\n";


            break;

            case 'clear':

                if(!$clear)
                {
                    die("\e[91mERROR:\e[0m Clear Object Missing.\n");
                }

                switch($clear)
                {
                    case 'logs':

                        if(!empty(Spry::config()->log_api_file) && file_exists(Spry::config()->log_api_file))
                        {
                            if(file_put_contents(Spry::config()->log_api_file, '') !== false)
                            {
                                echo "\e[92mCleared API Logs!\e[0m\n";
                            }
                            else
                            {
                                "\e[91mUnknown ERROR:\e[0m Clearing API Log.\n";
                            }
                        }
                        else
                        {
                            "\e[91mERROR:\e[0m Could not find API Log.\n";
                        }

                        if(!empty(Spry::config()->log_php_file) && file_exists(Spry::config()->log_php_file))
                        {
                            if(file_put_contents(Spry::config()->log_php_file, '') !== false)
                            {
                                echo "\e[92mCleared PHP Logs!\e[0m\n";
                            }
                            else
                            {
                                "\e[91mUnknown ERROR:\e[0m Clearing PHP Log.\n";
                            }
                        }
                        else
                        {
                            "\e[91mERROR:\e[0m Could not find PHP Log.\n";
                        }

                    break;

                    default:

                        die("\e[91mERROR:\e[0m Unknown Clear Command.\n");

                    break;
                }

            break;

            case 'init':

                echo "\n\e[96mSpry init complete!\e[0m\n";
                echo "Folder 'spry' created.\n";

                if(is_writable($config_file) && is_readable($config_file))
                {
                    $salt = sha1(rand(10000,99999).uniqid(mt_rand(), true).rand(10000,99999));
                    //echo $salt;
                    $config_contents = str_replace("config->salt = '';", "config->salt = '".$salt."';", file_get_contents($config_file));
                    if($config_contents)
                    {
                        if(file_put_contents($config_file, $config_contents))
                        {
                            echo "Salt value auto generated.\n";
                        }
                        else
                        {
                            echo "\e[91mERROR:\e[0m Could not update config file salt value.\n";
                        }

                        echo "Update the rest of your config file accordingly: ".$config_file."\n";
                    }
                }

                exit;

            break;

            case 'hash':

                if(!$hash)
                {
                    die("\e[91mERROR:\e[0m Missing Hash Value.  If hashing a value that has spaces then wrap with \"\"");
                }

                die(parent::get_hash($hash));

            break;

            case 'migrate':

                $migrate_args = [
                    'dryrun' => (in_array('--dryrun', $args) ? true : false),
                    'destructive' => (in_array('--destructive', $args) ? true : false),
                ];

                $response = parent::db_migrate($migrate_args);

                if(!empty($response['response']) && $response['response'] === 'error')
                {
                    if(!empty($response['messages']))
                    {
                        echo "\e[91mERROR:\e[0m\n";
                        echo implode("\n", $response['messages']);
                    }
                }
                elseif(!empty($response['response']) && $response['response'] === 'success')
                {
                    if(!empty($response['body']))
                    {
                        echo "\e[92mSuccess!\e[0m\n";
                        echo implode("\n", $response['body']);
                    }
                }

            break;

            case 'test':

                $total_time = 0;

                for($i=0; $i < $repeat; $i++)
                {
                    if($singletest)
                    {
                        echo "Running Test: ".$singletest."...\n";
                        $time_start = microtime(true);
                        $response = parent::test($singletest);
                        $time = number_format(microtime(true) - $time_start, 6);
                        $total_time+= $time;
                        if(!empty($response['response']) && $response['response'] === 'error')
                        {
                            if(!empty($response['messages']))
                            {
                                echo "\e[91mERROR:\e[0m (".$time." sec)\n";
                                echo implode("\n", $response['messages'])."\n";
                            }
                        }
                        elseif(!empty($response['response']) && $response['response'] === 'success')
                        {
                            if(!empty($response['body']))
                            {
                                echo "\e[92mSuccess!\e[0m (".$time." sec)\n";
                            }
                        }

                        if($verbose)
                        {
                            print_r($response);
                        }
                    }
                    else
                    {
                        $last_response = null;

                        $failed_tests = [];

                        if(empty(Spry::config()->tests))
                        {
                            $response = Spry::results(5052, null);
                            if(!empty($response['messages']))
                            {
                                echo "\e[91mERROR:\e[0m\n";
                                echo implode("\n", $response['messages'])."\n";
                                exit;
                            }
                        }

                        foreach (Spry::config()->tests as $test_name => $test)
                        {
                            foreach ($test['params'] as $param_key => $param)
                			{
                                if(!empty($last_response) && substr($param, 0, 1) === '{' && substr($param, -1) === '}')
                                {
                                    $path = explode('.', substr($param, 1, -1));
                                    $param_value = $last_response;
                                    foreach($path as $key)
                                    {
                                        if(isset($param_value[$key]))
                                        {
                                            $param_value = $param_value[$key];
                                        }
                                        else
                                        {
                                            $param_value = null;
                                            break;
                                        }
                                    }

                                    $test['params'][$param_key] = $param_value;
                                }
                			}

                            echo "\nRunning Test: ".$test_name."...\n";
                            $time_start = microtime(true);
                            $response = parent::test($test);
                            $time = number_format(microtime(true) - $time_start, 6);
                            $total_time+= $time;
                            if(!empty($response['response']) && $response['response'] === 'error')
                            {
                                $failed_tests[] = $test_name;
                                if(!empty($response['messages']))
                                {
                                    echo "\e[91mFailed:\e[0m (".$time." sec)\n";
                                    echo implode("\n", $response['messages'])."\n";
                                }
                            }
                            elseif(!empty($response['response']) && $response['response'] === 'success')
                            {
                                if(!empty($response['body']))
                                {
                                    echo "\e[92mSuccess!\e[0m (".$time." sec)\n";
                                }
                            }

                            if($verbose)
                            {
                                print_r($response);
                            }

                            $last_response = (!empty($response['body']['full_response']) ? $response['body']['full_response'] : null);
                        }

                        if(empty($failed_tests))
                        {
                            echo "\n\e[92mAll Tests Passed Successfully!\e[0m\n";
                        }
                        else
                        {
                            echo "\n\e[91mAll Failed Tests:\e[0m\n - ";
                            echo implode("\n - ", $failed_tests)."\n";
                        }
                    }
                }

                if($repeat > 1)
                {
                    echo "\n\e[92mTotal Time:\e[0m (".number_format($total_time, 6)." sec)\n";
                    echo "\e[92mAverage Time:\e[0m (".number_format(($total_time/$repeat), 6)." sec)\n";
                }

            break;

            case 'up':

                echo
                "Spry Server Running:\n".
                " API Endpoint --------- \e[96mhttp://localhost:8000\e[0m\n";

                if(Spry::config()->webtools_enabled && Spry::config()->webtools_endpoint )
                {
                    echo " WebTools Url --------- \e[96mhttp://localhost:8000".Spry::config()->webtools_endpoint."\e[0m\n";
                }

                echo "\n";
                echo "\e[37mPress Ctrl-C to quit....\e[0m";
            break;
        }
    }
}
