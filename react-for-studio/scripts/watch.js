const watch = require('node-watch');
const cmd = require('node-cmd');
const chalk = require('chalk');

watch(
    'src/',
    {
        recursive: true
    },
    function(evt, name) {
        console.log(
            chalk.green('[' + process.env.NODE_ENV + '] build start : ' + name)
        );
        let beforeDay = new Date();
        let beforeDate = `${beforeDay.toLocaleDateString()} ${beforeDay.toLocaleTimeString()}`;
        if (process.env.NODE_ENV === 'development') {
            cmd.get('yarn build:dev', function(err, data, stderr) {
                let afterDay = new Date();
                let afterDate = `${afterDay.toLocaleDateString()} ${afterDay.toLocaleTimeString()}`;
                if (err) {
                    console.log(chalk.red('build error : ' + data));
                } else {
                    console.log(
                        chalk.blue(
                            'build:prod [' +
                                beforeDate +
                                '][' +
                                afterDate +
                                '][' +
                                name +
                                '] file change success!!!'
                        )
                    );
                }
            });
        } else {
            cmd.get('yarn build:prod', function(err, data, stderr) {
                let afterDay = new Date();
                let afterDate = `${afterDay.toLocaleDateString()} ${afterDay.toLocaleTimeString()}`;
                if (err) {
                    console.log(chalk.red('build error : ' + data));
                } else {
                    console.log(
                        chalk.blue(
                            'build:prod [' +
                                beforeDate +
                                '][' +
                                afterDate +
                                '][' +
                                name +
                                '] file change success!!!'
                        )
                    );
                }
            });
        }
    }
);
