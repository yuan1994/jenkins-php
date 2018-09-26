<?php

$body = <<<JENKINS
由用户 tianpian 启动\n
在 master 上构建 在工作空间 /Users/Shared/Jenkins/Home/workspace/job-name 中\n
 > /usr/local/bin/git rev-parse --is-inside-work-tree # timeout=10\n
Fetching changes from the remote Git repository\n
 > /usr/local/bin/git config remote.origin.url https://git.xxx.com/tianpian/xxx # timeout=10\n
Fetching upstream changes from https://git.xxx.com/tianpian/xxx\n
 > /usr/local/bin/git --version # timeout=10\n
using GIT_ASKPASS to set credentials xxx\n
 > /usr/local/bin/git fetch --tags --progress https://git.xxx.com/tianpian/xxx +refs/heads/*:refs/remotes/origin/*\n
 > /usr/local/bin/git rev-parse refs/remotes/origin/master^{commit} # timeout=10\n
 > /usr/local/bin/git rev-parse refs/remotes/origin/origin/master^{commit} # timeout=10\n
Checking out Revision 531ee3946843d206465a95fd26d15fc4ed7e2467 (refs/remotes/origin/master)\n
 > /usr/local/bin/git config core.sparsecheckout # timeout=10\n
 > /usr/local/bin/git checkout -f 531ee3946843d206465a95fd26d15fc4ed7e2467\n
Commit message: "提交测试配置文件"\n
 > /usr/local/bin/git rev-list --no-walk 531ee3946843d206465a95fd26d15fc4ed7e2467 # timeout=10\n
[job-name] $ /bin/sh -xe /Users/Shared/Jenkins/tmp/jenkins7168493969386779570.sh\n
SSH: Connecting from host [mbp.local]\n
SSH: Connecting with configuration [my-dev] ...\n
SSH: Disconnecting configuration [my-dev] ...\n
SSH: Transferred 0 file(s)\n
Finished: SUCCESS
JENKINS;

return [
    'body' => $body,
];
