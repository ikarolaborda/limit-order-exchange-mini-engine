<script setup lang="ts">
import { Card, CardContent, Accordion, AccordionItem, AccordionTrigger, AccordionContent } from '@/components/ui'
</script>

<template>
  <Card class="border-border bg-card">
    <CardContent class="pt-6">
      <div class="mb-4 flex items-center gap-2">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="text-primary"
        >
          <circle cx="12" cy="12" r="10" />
          <path d="M12 16v-4" />
          <path d="M12 8h.01" />
        </svg>
        <h3 class="text-lg font-semibold">How Trading Works</h3>
      </div>

      <Accordion type="single" collapsible>
        <AccordionItem value="what-is-limit-order">
          <AccordionTrigger>What is a limit order?</AccordionTrigger>
          <AccordionContent>
            <p class="text-muted-foreground">
              A <strong class="text-foreground">limit order</strong> lets you specify the exact price at which you want to buy or sell.
              Unlike market orders that execute immediately at current prices, limit orders only execute when
              someone else is willing to trade at your price (or better).
            </p>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="how-matching-works">
          <AccordionTrigger>How does order matching work?</AccordionTrigger>
          <AccordionContent>
            <div class="space-y-3 text-muted-foreground">
              <p>
                When you place an order, the system looks for a <strong class="text-foreground">matching counter-order</strong>:
              </p>
              <ul class="ml-4 list-disc space-y-1">
                <li><strong class="text-foreground">Buy orders</strong> match with sell orders at <span class="text-green-600 dark:text-green-400">the same price or lower</span></li>
                <li><strong class="text-foreground">Sell orders</strong> match with buy orders at <span class="text-green-600 dark:text-green-400">the same price or higher</span></li>
                <li>Both orders must have the <strong class="text-foreground">same amount</strong></li>
              </ul>
              <p>
                If no match is found, your order enters the <strong class="text-foreground">orderbook</strong> and waits for a matching order from another trader.
              </p>
            </div>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="why-order-waiting">
          <AccordionTrigger>Why is my order "waiting"?</AccordionTrigger>
          <AccordionContent>
            <div class="space-y-3 text-muted-foreground">
              <p>
                Your order shows <strong class="text-foreground">"waiting"</strong> because there's currently no matching order in the book.
              </p>
              <p>This can happen when:</p>
              <ul class="ml-4 list-disc space-y-1">
                <li>Your <strong class="text-foreground">buy price is too low</strong> - no sellers willing to sell at that price</li>
                <li>Your <strong class="text-foreground">sell price is too high</strong> - no buyers willing to pay that price</li>
                <li>The <strong class="text-foreground">amount doesn't match</strong> any existing orders</li>
              </ul>
              <p class="rounded bg-muted/50 p-2 text-sm">
                <strong>Tip:</strong> Check the orderbook to see existing orders. Place your order at a price that overlaps with the other side to get an instant match!
              </p>
            </div>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="what-gets-locked">
          <AccordionTrigger>What gets locked when I place an order?</AccordionTrigger>
          <AccordionContent>
            <div class="space-y-3 text-muted-foreground">
              <p>To ensure you can fulfill your order when it matches:</p>
              <ul class="ml-4 list-disc space-y-1">
                <li>
                  <strong class="text-foreground">Buy orders</strong>: Your <span class="text-primary">USD balance</span> is locked
                  (price × amount + 1.5% fee)
                </li>
                <li>
                  <strong class="text-foreground">Sell orders</strong>: Your <span class="text-primary">crypto amount</span> is locked
                </li>
              </ul>
              <p>
                Locked funds are <strong class="text-foreground">released</strong> when:
              </p>
              <ul class="ml-4 list-disc space-y-1">
                <li>The order is <span class="text-green-600 dark:text-green-400">filled</span> (trade completes)</li>
                <li>You <span class="text-destructive">cancel</span> the order</li>
              </ul>
            </div>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="fees">
          <AccordionTrigger>What are the trading fees?</AccordionTrigger>
          <AccordionContent>
            <div class="space-y-3 text-muted-foreground">
              <p>
                A <strong class="text-foreground">1.5% fee</strong> is charged on the notional value (price × amount) of each trade.
              </p>
              <ul class="ml-4 list-disc space-y-1">
                <li>The fee is paid by the <strong class="text-foreground">buyer</strong></li>
                <li>It's locked upfront when placing a buy order</li>
                <li>Any excess locked funds are refunded after the trade</li>
              </ul>
            </div>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="test-trading">
          <AccordionTrigger>How can I test a trade?</AccordionTrigger>
          <AccordionContent>
            <div class="space-y-3 text-muted-foreground">
              <p>To see a trade execute:</p>
              <ol class="ml-4 list-decimal space-y-2">
                <li>Place a <strong class="text-foreground">SELL order</strong> (e.g., 0.1 BTC at $50,000)</li>
                <li>Open another browser or incognito window</li>
                <li>Login as a <strong class="text-foreground">different demo user</strong> (trader2@example.com)</li>
                <li>Place a <strong class="text-foreground">BUY order</strong> for the same amount at the same or higher price</li>
                <li>The orders will match instantly!</li>
              </ol>
              <p class="rounded bg-muted/50 p-2 text-sm">
                <strong>Demo accounts:</strong> trader1@example.com through trader4@example.com (password: <code class="rounded bg-muted px-1">password</code>)
              </p>
            </div>
          </AccordionContent>
        </AccordionItem>
      </Accordion>
    </CardContent>
  </Card>
</template>
