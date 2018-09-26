## Jenkins::getInfo
获取主节点信息或者信息中的某项

`Jenkins::getInfo($item = '', $query = '', $folderUrl = '') : array|mixed`

### 参数
名称 | 类型 | 是否必填 | 备注
:--- | :---: | :---: | :---
$item | string | N | 主节点信息中的某项值
$query | string | N | 附加的查询参数
$folderUrl | string | N | 根文件夹名称

### 返回值
```json
{
    "_class": "hudson.model.Hudson",
    "assignedLabels": [
        {
            "name": "master"
        }
    ],
    "mode": "NORMAL",
    "nodeDescription": "Jenkins的master节点",
    "nodeName": "",
    "numExecutors": 2,
    "description": null,
    "jobs": [
        {
            "_class": "hudson.model.FreeStyleProject",
            "name": "crf",
            "url": "http://localhost:8080/job/crf/",
            "color": "blue"
        },
        {
            "_class": "hudson.model.FreeStyleProject",
            "name": "job-name",
            "url": "http://localhost:8080/job/job-name/",
            "color": "yellow"
        },
        {
            "_class": "com.cloudbees.hudson.plugins.folder.Folder",
            "name": "Test",
            "url": "http://localhost:8080/job/Test/"
        },
        {
            "_class": "org.jenkinsci.plugins.workflow.multibranch.WorkflowMultiBranchProject",
            "name": "test001",
            "url": "http://localhost:8080/job/test001/"
        }
    ],
    "overallLoad": [],
    "primaryView": {
        "_class": "hudson.model.AllView",
        "name": "all",
        "url": "http://localhost:8080/"
    },
    "quietingDown": false,
    "slaveAgentPort": -1,
    "unlabeledLoad": {
        "_class": "jenkins.model.UnlabeledLoadStatistics"
    },
    "useCrumbs": false,
    "useSecurity": true,
    "views": [
        {
            "_class": "hudson.model.AllView",
            "name": "all",
            "url": "http://localhost:8080/"
        }
    ]
}

```

### 异常信息

`Yuan1994\Jenkins\Exceptions\JenkinsException`

Item[{$item}] does not exists.


// TODO 待完善其他API