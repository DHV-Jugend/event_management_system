<?php
/**
 * @author Christoph Bessei
 */

/** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

(function ($deployment) {
    /** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

    $nodeName = 'example.com';
    $appName = 'Event management system';

    // SSH with key auth
    $sshHost = 'example.com';
    $sshUsername = 'SSH_USER';

    // Exclude some files from deployment
    $rsyncExcludes = [
        '.idea',
        '.git',
        'tests/',
    ];

    // Is initial deployment?
    $initialDeployment = false;

    // Add additional directories
    $createDirectories = [];

    // Merge (copy!) shared folders with folders from VCS. The files inside the shared folder overwrite the VCS files
    $mergeSharedDirectories = [];

    // Deployment path, absolute path to project root folder on $node
    $deploymentPath = '/var/www/html';

    // Git repository of project
    $repositoryUrl = 'git@github.com:DHV-Jugend/event_management_system.git';

    // Application web directory.
    $webDirectory = '';

    // Config for WebOpcacheResetExecuteTask
    $enableOpcacheClearTask = false;
    // Absolute url to application frontend, only needed if $enableOpcacheClearTask is true
    $baseUrl = 'https://example.com/';

    // Local composer path (if not in PATH)
    $composerPath = 'composer';

    // Additional options for the application
    $additionalApplicationOptions = [];

    $warmupScripts = [];

    /**********************************************/
    /* That's all, stop editing! Happy deploying. */
    /**********************************************/

    // Configure node
    $node = new \TYPO3\Surf\Domain\Model\Node($nodeName);
    $node->setHostname($sshHost);
    $node->setOption('username', $sshUsername);
    $node->setOption('composerCommandPath', $composerPath);

    // Configure application
    $application = new \BIT\Typo3SurfExtended\Application\PhpApplication($appName);
    $application->setDeploymentPath($deploymentPath);
    $application->setOption('keepReleases', 2);
    $application->setOption('repositoryUrl', $repositoryUrl);
    if (!empty($branch)) {
        $application->setOption('branch', $branch);
    }
    $application->setOption('webDirectory', $webDirectory);
    $application->setOption('mergeSharedFolders', $mergeSharedDirectories);
    foreach ($symlinks ?? [] as $linkPath => $sourcePath) {
        $application->addSymlink($linkPath, $sourcePath);
    }
    $application->setOption('rsyncExcludes', $rsyncExcludes);
    $application->setOption('warmupScripts', $warmupScripts);
    $application->setOption('clearOpcache', $enableOpcacheClearTask);
    $application->setOption('baseUrl', $baseUrl);
    $application->setOption(
        'scriptBasePath',
        \TYPO3\Flow\Utility\Files::concatenatePaths([$deployment->getWorkspacePath($application), $webDirectory])
    );
    foreach ($additionalApplicationOptions as $key => $value) {
        $application->setOption($key, $value);
    }

    $application->addNode($node);
    $deployment->addApplication($application);

    $deployment->onInitialize(
        function () use ($deployment, $application) {
            $workflow = $deployment->getWorkflow();
            $workflow->defineTask(
                'bit.event_management_system.buildAssets',
                \TYPO3\Surf\Task\LocalShellTask::class,
                ['command' => 'cd {workspacePath} && ls -lah && cd assets/ && npm install && grunt dist']
            );
            $workflow->addTask('bit.event_management_system.buildAssets', 'package', $application);
        }
    );
})(
    $deployment
);
