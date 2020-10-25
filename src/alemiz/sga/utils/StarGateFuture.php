<?php
/*
 * Copyright 2020 Alemiz
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace alemiz\sga\utils;

use Closure;
use Exception;

class StarGateFuture {


    /**
     * Closure should accept two parameters.
     * closure(result, exception)
     * @var Closure[]
     */
    private $closures = [];

    /**
     * @param mixed $response
     */
    public function complete($response) : void {
        if (empty($this->closures)){
            return;
        }

        foreach ($this->closures as $closure){
            $closure($response, null);
        }
    }

    /**
     * @param Exception $e
     */
    public function completeExceptionally(Exception $e) : void {
        if (empty($this->closures)){
            return;
        }

        foreach ($this->closures as $closure){
            $closure(null, $e);
        }
    }

    /**
     * @param Closure $closure
     */
    public function whenComplete(Closure $closure) : void {
        $this->closures[] = $closure;
    }

}