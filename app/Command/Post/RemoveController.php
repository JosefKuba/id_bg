<?php

declare(strict_types=1);

namespace App\Command\Post;

use Minicli\Command\CommandController;

class RemoveController extends CommandController
{
    public function handle(): void
    {
        $postsFile = POST_INPUT_PATH . "posts.tsv";
        $posts = getLine($postsFile);

        $pageIdFile = POST_INPUT_PATH . "ids";
        $pageIds = getLine($pageIdFile);

        $results = [];
        foreach ($posts as $post) {
            $postArr = explode("\t", $post);
            $pageId  = array_pop($postArr);

            if (in_array($pageId, $pageIds)) {
                continue;
            }

            $results[] = $post;
        }

        $resutltFile = POST_OUTPUT_PATH . CURRENT_TIME . " results.tsv";
        file_put_contents($resutltFile, implode(PHP_EOL, $results));
    }
}
