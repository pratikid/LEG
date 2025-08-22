<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Event;
use ReflectionClass;

/**
 * Trait for handling event operations
 */
trait HandlesEventOperations
{
    /**
     * Dispatch an event.
     *
     * @param object $event The event to dispatch
     * @param bool $halt Whether to halt after first listener that returns non-null
     * @return mixed The event dispatch result
     */
    protected function dispatchEvent(object $event, bool $halt = false)
    {
        return $halt ? Event::until($event) : Event::dispatch($event);
    }
    
    /**
     * Dispatch an event and forget about it (fire and forget).
     *
     * @param object $event The event to dispatch
     * @return void
     */
    protected function dispatchEventAndForget(object $event): void
    {
        Event::dispatch($event);
    }
    
    /**
     * Dispatch an event with a payload.
     *
     * @param string $event The event name
     * @param array $payload The event payload
     * @param bool $halt Whether to halt after first listener that returns non-null
     * @return mixed The event dispatch result
     */
    protected function dispatchEventWithPayload(string $event, array $payload = [], bool $halt = false)
    {
        return $halt ? Event::until($event, $payload) : Event::dispatch($event, $payload);
    }
    
    /**
     * Register an event listener with the dispatcher.
     *
     * @param string|array $events The event(s) to listen for
     * @param mixed $listener The listener callback
     * @return void
     */
    protected function listenForEvent($events, $listener): void
    {
        Event::listen($events, $listener);
    }
    
    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param object|string $subscriber The subscriber
     * @return void
     */
    protected function subscribeToEvents($subscriber): void
    {
        Event::subscribe($subscriber);
    }
    
    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param string $event The event to remove listeners for
     * @return void
     */
    protected function forgetEventListeners(string $event): void
    {
        Event::forget($event);
    }
    
    /**
     * Get all registered events with the dispatcher.
     *
     * @return array The registered events
     */
    protected function getRegisteredEvents(): array
    {
        return Event::getListeners();
    }
    
    /**
     * Get all listeners for a given event.
     *
     * @param string $event The event name
     * @return array The event listeners
     */
    protected function getEventListeners(string $event): array
    {
        return Event::getListeners($event);
    }
    
    /**
     * Determine if a given event has listeners.
     *
     * @param string $event The event name
     * @return bool True if the event has listeners, false otherwise
     */
    protected function hasEventListeners(string $event): bool
    {
        return Event::hasListeners($event);
    }
    
    /**
     * Register a model event observer.
     *
     * @param string $model The model class
     * @param string $observer The observer class
     * @return void
     */
    protected function observeModel(string $model, string $observer): void
    {
        $model::observe($observer);
    }
    
    /**
     * Get all model events that can be observed.
     *
     * @param string $model The model class
     * @return array The observable events
     */
    protected function getObservableModelEvents(string $model): array
    {
        return (new $model)->getObservableEvents();
    }
    
    /**
     * Get all methods in an event listener class.
     *
     * @param string $listenerClass The listener class
     * @return array The listener methods
     */
    protected function getEventListenerMethods(string $listenerClass): array
    {
        $reflection = new ReflectionClass($listenerClass);
        $methods = [];
        
        foreach ($reflection->getMethods() as $method) {
            if ($method->isPublic() && !$method->isConstructor() && !$method->isDestructor()) {
                $methods[] = $method->getName();
            }
        }
        
        return $methods;
    }
    
    /**
     * Dispatch an event to a specific listener.
     *
     * @param object $event The event to dispatch
     * @param object $listener The listener object
     * @param string $method The listener method to call
     * @return mixed The listener result
     */
    protected function dispatchEventToListener(object $event, object $listener, string $method = 'handle')
    {
        return $listener->$method($event);
    }
    
    /**
     * Create and dispatch a model event.
     *
     * @param string $model The model class
     * @param string $event The event name (created, updated, deleted, etc.)
     * @param mixed $modelInstance The model instance
     * @return mixed The event dispatch result
     */
    protected function dispatchModelEvent(string $model, string $event, $modelInstance)
    {
        return $model::fireModelEvent($event, false, $modelInstance);
    }
    
    /**
     * Get the event dispatcher instance.
     *
     * @return \Illuminate\Contracts\Events\Dispatcher The event dispatcher
     */
    protected function getEventDispatcher(): Dispatcher
    {
        return Event::getFacadeRoot();
    }
    
    /**
     * Set the event dispatcher instance.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $dispatcher The event dispatcher
     * @return void
     */
    protected function setEventDispatcher(Dispatcher $dispatcher): void
    {
        Event::setFacadeRoot($dispatcher);
    }
    
    /**
     * Determine if events are dispatched immediately or queued.
     *
     * @return bool True if events are dispatched immediately, false otherwise
     */
    protected function areEventsQueueable(): bool
    {
        return method_exists(Event::getFacadeRoot(), 'shouldQueue') && 
               Event::getFacadeRoot()->shouldQueue();
    }
    
    /**
     * Flush all queued events.
     *
     * @return void
     */
    protected function flushQueuedEvents(): void
    {
        if (method_exists(Event::getFacadeRoot(), 'flush')) {
            Event::getFacadeRoot()->flush();
        }
    }
    
    /**
     * Dispatch an event with a delay.
     *
     * @param object $event The event to dispatch
     * @param int $delay The delay in seconds
     * @return void
     */
    protected function dispatchEventWithDelay(object $event, int $delay): void
    {
        // Check if the event implements ShouldQueue
        if (method_exists($event, 'delay')) {
            $event->delay($delay);
        }
        
        Event::dispatch($event);
    }
    
    /**
     * Dispatch an event to a specific queue.
     *
     * @param object $event The event to dispatch
     * @param string $queue The queue name
     * @return void
     */
    protected function dispatchEventToQueue(object $event, string $queue): void
    {
        // Check if the event implements ShouldQueue
        if (method_exists($event, 'onQueue')) {
            $event->onQueue($queue);
        }
        
        Event::dispatch($event);
    }
    
    /**
     * Dispatch an event to a specific connection.
     *
     * @param object $event The event to dispatch
     * @param string $connection The connection name
     * @return void
     */
    protected function dispatchEventToConnection(object $event, string $connection): void
    {
        // Check if the event implements ShouldQueue
        if (method_exists($event, 'onConnection')) {
            $event->onConnection($connection);
        }
        
        Event::dispatch($event);
    }
    
    /**
     * Dispatch a batch of events.
     *
     * @param array $events The events to dispatch
     * @return void
     */
    protected function dispatchEventBatch(array $events): void
    {
        foreach ($events as $event) {
            Event::dispatch($event);
        }
    }
    
    /**
     * Register a wildcard event listener.
     *
     * @param string $pattern The event pattern
     * @param callable $listener The listener callback
     * @return void
     */
    protected function listenForWildcardEvent(string $pattern, callable $listener): void
    {
        Event::listen($pattern, $listener);
    }
}